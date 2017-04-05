<?php

class Netstarter_Tbyb_Model_FuturePayment extends Netstarter_Shelltools_Model_Shared_Abstract
{
    protected $_jobId = 'TBYB_CHARGE_CUSTOMERS';
    
    protected $_dateParam = null;
    
    const XML_PATH_EMAIL_TBYB_TEMPLATE_CHARGED = 'curvesence/tbyb/template_charged';
    const XML_PATH_EMAIL_TBYB_TEMPLATE_CANCELLED = 'curvesence/tbyb/template_cancelled';
    
    public function setDate($date = null)
    {
        $this->_dateParam = $date;
    }
    
    public function getDate()
    {
        return $this->_dateParam;
    }
            
    protected function _update()
    {
        if (!Mage::helper("netstarter_tbyb")->isEnabled())
        {
            return true; 
        }
        
        if ($this->getDate() != null)
        {
            $this->processFuturePayment($this->getDate());
        }
        else
        {
            $this->processFuturePayment();
        }
    }
    
    protected function _getEmails($configPath, $storeId)
    {
        $data = Mage::getStoreConfig($configPath, $storeId);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }
    
    public function processFuturePayment($paymentDate = null){
        if (!Mage::helper("netstarter_tbyb")->isEnabled()) { 
            return true; 
        }
        
        $this->_log("");
        $this->_log("STARTED PROCESS AT : " . date('Y-m-d H:i:s'));
        try{
            date_default_timezone_set("Australia/Sydney");
            $nowAUStart = date("Y-m-d");
            $nowAUEnd = date("Y-m-d H:i:s");
            $nowAUNow = date("Y-m-d");
            if($paymentDate){
                $nowAUStart = $paymentDate;
                $nowAUEnd = $paymentDate . " " . date("H:i:s");
            }
            $dateDiffAU = strtotime($nowAUEnd) - strtotime($nowAUStart);
            
            date_default_timezone_set("UTC");
            
            $nowUTCEnd = date("Y-m-d H:i:s");
            $nowUTCNow = date("Y-m-d");
            if($paymentDate){
                $nowUTCEnd = date("Y-m-d",strtotime($paymentDate) - ((strtotime($nowAUNow)-strtotime($nowUTCNow)))) . " " . date("H:i:s");
            }
            $nowUTCStart = strtotime($nowUTCEnd) - $dateDiffAU;
            $nowUTCStart = date("Y-m-d H:i:s", $nowUTCStart);
            $nowUTCEnd = strtotime($nowUTCEnd) - $dateDiffAU + 86399;
            $nowUTCEnd = date("Y-m-d H:i:s", $nowUTCEnd);
            
            //Capture payment by website
            $websitePayment = $this->getFuturePayments($nowUTCEnd);
            foreach($websitePayment as $key=>$p){
                $this->processPayment($key, $p);
            }
            //Destory cancelled tokens
            $this->destroyTokensOfCancelledItems($nowUTCStart, $nowUTCEnd);
        }catch(Exception $e){
            $this->_log("ERROR : " . $e->getMessage());
        }
        
        $this->_log("END PROCESS AT : " . date('Y-m-d H:i:s'));
    }
    
    private function processPayment($webSiteId, $paymentOrder){
        $itemsStatus = Mage::getModel('netstarter_tbyb/status')->getOptionsArray();
        
        $api = Mage::getModel("netstarter_eway/rapid31");
        
        //Process payment by order
        foreach($paymentOrder as $order){
            try{
                $this->_log("");
                $this->_log("----- Processing customer " . $order->getCustomerId() . " in website " . $webSiteId);
                
                $this->_log("Amount to be charged is: " . $order->getAmount());
                
                if ($order->getAmount() > 0)
                {
                    //Set the store to get correct api configuration
                    $website = Mage::getModel('core/website')->load($webSiteId);
                    $store = $website->getDefaultStore();
                    Mage::app()->setCurrentStore($store->getStoreId());
                    
                    $result = $api->payWithToken($order->getToken(), $order->getAmount());
                    $status = $result && $result->TransactionStatus == "1"?Netstarter_Tbyb_Model_Status::STATUS_SUCCESS:Netstarter_Tbyb_Model_Status::STATUS_FAILURE;
                }
                else
                {
                    $result = false;
                    $status = Netstarter_Tbyb_Model_Status::STATUS_SUCCESS;
                }
                
                $this->_log(sprintf("Payment information : Order Increment Id:%s, Customer Id:%s, Transaction Id:%s, Response Message:%s, Response Code:%s", $order->getIncrementId(), $order->getCustomerId(), ($result && $result->TransactionID?$result->TransactionID:""), ($result && $result->ResponseMessage?$result->ResponseMessage:""), ($result && $result->ResponseCode?$result->ResponseCode:"")));

                //Save the status of payment under line items.
                foreach($order->getItems() as $item){
                    $item->setStatus($status);
                    $item->setUpdatedAt(time());
                    $item->setTransactionId($result && $result->TransactionID?$result->TransactionID:"");
                    $item->setResponseMessage($result && $result->TransactionID?$result->TransactionID:"");
                    $item->save();
                    $this->_log(sprintf("Processing Item Id:%s, Sku:%s, Price:%s, Status:%s", $item->getItemId(), $item->getSku(), $item->getPrice(), $itemsStatus[$status]));
                }

                //destroy the token if the payment is successfull
                if($status == Netstarter_Tbyb_Model_Status::STATUS_SUCCESS){
                    //Create invoice
                    $this->createInvoice($order, $result->TransactionID);
                }
                //Check wether customer has more future payment. if not destroy and delete token
                if(!$this->hasFuturePayments($order->getCustomerId(), $webSiteId)){
                    $this->_log("Token destroyed for customer " . $order->getCustomerId() . " in website " . $webSiteId);

                    //Set the store to get correct api configuration
                    $website = Mage::getModel('core/website')->load($webSiteId);
                    $store = $website->getDefaultStore();
                    Mage::app()->setCurrentStore($store->getStoreId());

                    //Destroy token from eway
                    $api->destroyToken($order->getToken());
                    //Delete token from database
                    $order->getToken()->delete();
                }
            }catch (Exception $e)
            {
                $this->_log("ERROR CHARGING CUSTOMER : " . $e->getMessage());
            }
        }
    }
    
    private function createInvoice($orderPayment, $transactionId){
        try{
            $cancelledItems = Mage::getModel('netstarter_tbyb/item')
                                    ->getCollection()
                                    ->addFieldToFilter('status', array('in' => array(Netstarter_Tbyb_Model_Status::STATUS_CANCELLED)))
                                    ->addFieldToFilter('increment_id', array('eq' => $orderPayment->getIncrementId()));
            
            $itemList = array();
            foreach($orderPayment->getItems() as $item){
                $itemList[$item->getOrderItemId()] = $item->getQty();
            }
            foreach($cancelledItems as $citem){
                $itemList[$citem->getOrderItemId()] = 0;
            }

            $amount = $orderPayment->getAmount();
            $comment = "Captured amount of " . Mage::app()->getLocale()->currency($orderPayment->getCurrencyCode())->getSymbol() . $amount . " online. Transaction ID: \"" . $transactionId . "\"";
            $invoiceId = Mage::getModel('sales/order_invoice_api')->create($orderPayment->getIncrementId(), $itemList , $comment, false, false);
            $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
            $invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_PAID);

            $invoice->save();

            //Save order details
            $order = $invoice->getOrder();
            $order->setTotalDue($order->getTotalDue() - $amount);
            $order->setBaseTotalDue($order->getBaseTotalDue() - $amount);
            $order->setBaseTotalPaid($order->getBaseTotalPaid() + $amount);
            $order->setTotalPaid($order->getTotalPaid() + $amount);
            $order->addStatusToHistory($order->getStatus(), $comment, false);

            //Save payment details
            $payment = Mage::getModel('sales/order_payment');
            $payment->setMethod(Mage::getStoreConfig(Netstarter_Tbyb_Model_Source_Payment::XML_PATH));
            $payment->setBaseAmountPaid($amount);
            $payment->setBaseAmountAuthorized($amount);
            $payment->setBaseAmountPaidOnline($amount);
            $payment->setAmountPaid($amount);
            $payment->setAmountAuthorized($amount);
            $payment->setAmountPaidOnline($amount);
            $payment->setParentId($order->getId());
            $payment->setLastTransId($transactionId);
            $payment->setAdditionalInformation($transactionId);
            $payment->save();

            //Save transaction
            $transaction = Mage::getModel('sales/order_payment_transaction');
            $transaction->setOrderPaymentObject($payment);
            $transaction->setOrderId($order->getId());
            $transaction->setPaymentId($payment->getEntityId());
            $transaction->setTxnId($transactionId);
            $transaction->setIsClosed(1);
            $transaction->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
            $transaction->save();

            $order->save();
            
//            $order->sendNewOrderEmail();
            $this->sendEmail ($order, self::XML_PATH_EMAIL_TBYB_TEMPLATE_CHARGED);
            
        }catch(Exception $e){
            $this->_log("ERROR CREATING INVOICE : " . $e->getMessage());
        }
    }
    
    private function hasFuturePayments($customerId, $websiteID){
        $items = Mage::getModel('netstarter_tbyb/item')
                    ->getCollection()
                    ->addFieldToFilter('status', array('in' => array(Netstarter_Tbyb_Model_Status::STATUS_TOBECHARGED)))
                    ->addFieldToFilter('customer_id', array('eq' => $customerId))
                    ->addFieldToFilter('website_id', array('eq' => $websiteID));
        return count($items)>0;
    }
    
    private function getFuturePayments($nowUTCEnd){
        $items = Mage::getModel('netstarter_tbyb/item')
                ->getCollection()
                ->addFieldToFilter('status', array('in' => array(Netstarter_Tbyb_Model_Status::STATUS_TOBECHARGED)))
                ->addFieldToFilter('future_payment_date', array('lteq' => $nowUTCEnd));
        
        $websitePayment = array();
        //Create array first by website then by order
        //Then capture payment first loop through website followed by order.
        $customerList = array();
        foreach($items as $item){
            $amount = $item->getPrice();
            $p = array();
            $orderPayments = new Varien_Object();
            if(isset($websitePayment[$item->getWebsiteId()])){
                $p = $websitePayment[$item->getWebsiteId()];
            }
            $itemList = array();
            if(isset($p[$item->getIncrementId()])){
                $orderPayments = $p[$item->getIncrementId()];
                $amount += $orderPayments->getAmount();
                $itemList = $orderPayments->getItems();
            }
            $itemList[] = $item;
            $orderPayments->setData("amount", $amount);
            $orderPayments->setData("items", $itemList);
            $orderPayments->setData("currency_code", $item->getCurrencyCode());
            $orderPayments->setData("customer_id", $item->getCustomerId());
            $orderPayments->setData("increment_id", $item->getIncrementId());
            $orderPayments->setData("website_id", $item->getWebsiteId());
            $p[$item->getIncrementId()] = $orderPayments;
            $websitePayment[$item->getWebsiteId()] = $p;
            if(!isset($customerList[$item->getCustomerId()])){
                $customerList[$item->getCustomerId()] = $item->getCustomerId();
            }
        }
        
        //Get all token of list of customer
        $tokens = Mage::getModel('netstarter_eway/token')
                    ->getCollection()
                    ->addFieldToFilter('customer_id', array('in' => array_keys($customerList)))
                    ->addFieldToFilter('website_id', array('in' => array_keys($websitePayment)));
        
        //Assign tokens to orders
        foreach($tokens as $token){
            foreach($websitePayment[$token->getWebsiteId()] as $p){
                if($p->getCustomerId() == $token->getCustomerId() && $token->getWebsiteId() == $p->getWebsiteId()){
                    $p->setData("token", $token);
                }
            }
        }
        return $websitePayment;
    }
    
    public function destroyTokensOfCancelledItems($nowUTCStart, $nowUTCEnd)
    {
        $this->_log("");
        $this->_log("START PROCESSING CANCELLED ITEMS...");

        $items = Mage::getModel('netstarter_tbyb/item')
            ->getCollection()
            ->addFieldToFilter('status', array('in' => array(Netstarter_Tbyb_Model_Status::STATUS_CANCELLED)))
            ->addFieldToFilter('future_payment_date', array('gteq' => $nowUTCStart))
            ->addFieldToFilter('future_payment_date', array('lteq' => $nowUTCEnd));

        $tokenLoader = Mage::getModel('netstarter_eway/token');
        
        foreach ($items as $item)
        {
                $website = Mage::getModel('core/website')->load($item->getWebsiteId());
                $store = $website->getDefaultStore();

            $order = Mage::getModel('sales/order')->load($item->getOrderId());
            
            $this->sendEmail ($order, self::XML_PATH_EMAIL_TBYB_TEMPLATE_CANCELLED, $store->getStoreId());
            
            if (!$this->hasFuturePayments($item->getCustomerId(), $item->getWebsiteId()))
            {

                Mage::app()->setCurrentStore($store->getStoreId());

                $token = $tokenLoader->loadByCustomerId($item->getCustomerId(), $item->getWebsiteId());
                $api = Mage::getModel("netstarter_eway/rapid31");

                if (is_object($token))
                {
                    $this->_log("Token destroyed for customer " . $item->getCustomerId() . " in website " . $item->getWebsiteId());
                    
                    $api->destroyToken($token);
                    $token->delete();
                }
            }
        }
    }
    
    public function sendEmail (Mage_Sales_Model_Order $order = null, $type = "", $storeParam = 0)
    {
        if (is_object($order))
        {
            $storeId = $order->getStore()->getId();
            $parameters = array(
                'order'        => $order
            );
        }
        else
        {
            $storeId = $storeParam;
            $parameters = array();
        }

        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(Mage_Sales_Model_Order::XML_PATH_EMAIL_COPY_TO, $storeId);
        $copyMethod = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_COPY_METHOD, $storeId);

        $templateId = Mage::getStoreConfig($type, $storeId);

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($order->getCustomerEmail(), $order->getCustomerFirstname());
        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams($parameters);
        $mailer->send();

        return $this;
    }
}