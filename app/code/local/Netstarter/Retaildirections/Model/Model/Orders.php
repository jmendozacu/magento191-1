<?php

/*
 * -> To get the store id: $this->getStoreId()
 * -> To get standard supply channel id: $this->getSupplyChannelId()
 * -> If new global constants are needed to be added to the code, it can
 * be added to Magento Backend and a getter can be added to the parent class Netstarter_Retaildirections_Model_Abstract.
 */

/**
 * Class Netstarter_Retaildirections_Model_Model_Product
 *
 * Class that imports products from the API to the Database.
 * Uses Netstarter_Retaildirections_Model_Client_Connection to handle soap connection.
 *
 */
class Netstarter_Retaildirections_Model_Model_Orders extends Netstarter_Retaildirections_Model_Model_Abstract
{

    /**
     * Service on the API to get order details.
     */
    const API_METHOD_ORDER_GET = 'SalesOrderGet';

    const API_METHOD_ORDER_SUBMIT = 'SalesOrderSubmit';

    const API_METHOD_ORDER_FINALIZE = 'SalesOrderFinalise';

    const API_METHOD_ORDER_HISTORY = 'SalesHistoryFind';

    const API_ORDER_INITIAL_STATUS = 'Pending';

    const API_ORDER_AFTER_STATUS = 'Approved';

    const ORDER_CONFIG_PATH = 'netstarter_retaildirections/order';


    protected $_jobId = 'ORDERS';
    protected $_logReportMode = self::LOG_REPORT_MODE_LOG;
    protected $_logXmlPath = 'netstarter_retaildirections/order/log_file';

    private function getQuote()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        return $quote;
    }

    public function getOrderConfigs($path = null)
    {
        return Mage::getStoreConfig(self::ORDER_CONFIG_PATH . $path);
    }

    /**
     * @todo
     *
     * @param $orderCode
     * @param $customerId
     */
    public function getSalesOrder($orderCode, $customerId)
    {

        if (!empty($orderCode) && !empty($customerId)) {


            $params = new SimpleXMLElement(self::XML_ROOT_NODE);
            $order = $params->addChild(self::API_METHOD_ORDER_GET);

            $order->addChild('salesorderCode', $orderCode);
            $order->addChild('customerId', $customerId);
            /*
             * Actually does the API call and return the XML.
             */
            $result = $this->getConnectionModel()->getResult(self::API_METHOD_ORDER_GET, $params);

            if (isset($result->SalesOrderDetail)) {

                return $result->SalesOrderDetail;
            } else {
                return false;
            }

        }
    }


    /**
     * submit order
     *
     * @param $elements
     */
    protected function _submitOrder($elements)
    {

        $params = new SimpleXMLElement(self::XML_ROOT_NODE);

        if (!empty($elements['SalesOrderDetail'])) {

            $details = $params->addChild('SalesOrderDetail');
            if (!empty($elements['storeCode'])) // eBay override
                $details->addChild('storeCode', $elements['storeCode']);
            else
                $details->addChild('storeCode', $this->getStoreId());
            $details->addChild('supplychannelCode', $this->getSupplyChannelId());

            foreach ($elements['SalesOrderDetail'] as $node => $element) {

                $details->addChild($node, $element);
            }
        }


        if (!empty($elements['SalesOrderLines'])) {

            $ordersLines = $params->addChild('SalesOrderLines');

            foreach ($elements['SalesOrderLines'] as $orderLine) {


                $ordersLine = $ordersLines->addChild('SalesOrderLine');

                foreach ($orderLine as $node => $element) {

                    if (!is_array($element))
                        $ordersLine->addChild($node, $element);
                }

                $ordersLinesTaxes = $ordersLine->addChild('SalesOrderLineTaxes');

                if (!empty($orderLine['SalesOrderLineTaxes'])) {

                    foreach ($orderLine['SalesOrderLineTaxes'] as $salesOrderLineTax) {

                        $ordersLinesTax = $ordersLinesTaxes->addChild('SalesOrderLineTax');

                        foreach ($salesOrderLineTax as $node => $element) {

                            if (!is_array($element))
                                $ordersLinesTax->addChild($node, $element);
                        }
                    }
                }
            }
        }


        $result = $this->getConnectionModel()->getResult(self::API_METHOD_ORDER_SUBMIT, $params);


        if (!empty($result->SalesOrderDetail)) {

            return $result->SalesOrderDetail;

        } elseif (!empty($result->ErrorResponse)) {

            throw new Exception("Order submit: {$result->ErrorResponse->errorMessage}", (int)$result->ErrorResponse->errorNumber);

        }

        return false;
    }

    /**
     * @todo verification is required to be done
     *
     *
     * Finalize order
     *
     */
    protected function _salesOrderFinalise($elements)
    {


        $params = new SimpleXMLElement(self::XML_ROOT_NODE);

        if (!empty($elements['ConfirmationDetail'])) {

            $details = $params->addChild("ConfirmationDetail");


            foreach ($elements['ConfirmationDetail'] as $node => $element) {

                $details->addChild($node, $element);
            }
        }


        if (!empty($elements['PaymentDetails'])) {

            $payments = $params->addChild("PaymentDetails");

            foreach ($elements['PaymentDetails'] as $payment) {


                $paymentDetail = $payments->addChild("PaymentDetail");

                foreach ($payment as $node => $element) {

                    $paymentDetail->addChild($node, $element);
                }

            }
        }


        $result = $this->getConnectionModel()->getResult(self::API_METHOD_ORDER_FINALIZE, $params);

        if (!empty($result->ConfirmationDetail)) {

            return $result->ConfirmationDetail;

        } elseif (!empty($result->ErrorResponse)) {

            throw new Exception("Order finalise: {$result->ErrorResponse->errorMessage}", (int)$result->ErrorResponse->errorNumber);

        }

        return false;
    }

    private function prepareTotals($totalsCart)
    {

        $totals = array('shipping' => null, 'giftcard' => null, 'discount' => null, 'giftwrapping' => null);

        foreach ($totalsCart as $k => $total) {

            switch ($k) {

                case 'shipping':
                    $totals['shipping'] = array('code' => $total->getCode(), 'value' => $total->getValue());
                    break;

                case 'giftcardaccount':

                    $giftCards = $total->getGiftCards();

                    if (!empty($giftCards)) {

                        $totals['discount'] -= $total->getValue();
                        $totals['giftcard'] = $total->getGiftCards();
                    }

                    break;

                case 'giftwrapping':

                    $giftBasePrice = $total->getGwPrice();
                    $totals['giftwrapping'] = ($giftBasePrice) ? $giftBasePrice : $total->getGwItemsPrice();
                    break;
            }
        }

        return $totals;
    }


    public function createOrders($order, $quote)
    {
        try {
//            $quote = $this->getQuote();
                $orderConfigs = $this->getOrderConfigs();
                $ordersData = null;
                $confirmData = null;

                $paymentConfigs = (isset($orderConfigs['payment_methods'])) ? unserialize($orderConfigs['payment_methods']) : null;
                $shippingConfig = (isset($orderConfigs['shipping_methods'])) ? unserialize($orderConfigs['shipping_methods']) : null;
                $giftWrapCode = (isset($orderConfigs['giftwrapcode'])) ? $orderConfigs['giftwrapcode'] : null;
                $giftWrapunitprice = (isset($orderConfigs['giftwrapunitprice'])) ? $orderConfigs['giftwrapunitprice'] : 5.99;
                $isPObox = false;
                $isGPobox = false;
                $totals = $this->prepareTotals($quote->getTotals());
                $shippingAddress = $order->getShippingAddress();

                /* $shippingAddress = ($quote->getShippingAddress()->getSameAsBilling()) ?
                     $order->getBillingAddress() :
                     $order->getShippingAddress();*/

                if (!$shippingAddress) {
                    $shippingAddress = $order->getBillingAddress();
                }

                $customer = $order->getCustomer();

                $locationRef = $shippingAddress->getLocationRef();
                $customerRdId = $customer->getRdId();

                if ($shippingAddress) {

                    $rdCustomer = Mage::getModel('netstarter_retaildirections/model_customer');
                    $address = str_replace(".", "", $shippingAddress->getStreet());

                    $address = str_replace(" ", "", $address);
                    if (strlen($address[0]) > 5) {
                        $POaddress = strtoupper(substr($address[0], 0, 5));
                        if ($POaddress == "POBOX") {
                            $isPObox = true;
                        }
                    }
                    if (strlen($address[0]) > 6) {
                        $GPOaddress = strtoupper(substr($address[0], 0, 6));
                        if ($GPOaddress == "GPOBOX") {
                            $isGPobox = true;
                        }
                    }

                    if (empty($customerRdId)) {

                        if ($order->getCustomerIsGuest()) {

                            $customer->setData(array('firstname' => htmlspecialchars($order->getCustomerFirstname()),
                                'lastname' => htmlspecialchars($order->getCustomerLastname()),
                                'email' => $order->getCustomerEmail(),
                                'is_guest' => true));
                        }
                        $customerId=$order->getCustomerId();
                        $afterpay=$order->getPayment()->getMethod();
                        //Mage::log('cust:'.$customerId.',method:'.$afterpay, null, 'orderafterpay.log');
                        if (empty($customerId) && $afterpay == 'afterpaypayovertime'){
                            $customer->setData(array('firstname' => htmlspecialchars($order->getCustomerFirstname()),
                                'lastname' => htmlspecialchars($order->getCustomerLastname()),
                                'email' => $order->getCustomerEmail(),
                                'is_guest' => true));

                            $this->customAttributeUpdate($order->getEntityId(),
                                array('customer_is_guest' => 1));
                        }

                        $customerRdId = $rdCustomer->createCustomer($customer);
                        $customer->setRdId($customerRdId);
                    }

                    if (empty($locationRef)) {


                        $locationRef = $rdCustomer->createCustomerSite($customer, $shippingAddress);
                        $shippingAddress->setLocationRef($locationRef);
                    }

                    $ordersData = array('SalesOrderDetail' => null, 'SalesOrderLines' => null);
                    $ordersData['SalesOrderDetail']['customerId'] = $customerRdId;
                    $ordersData['SalesOrderDetail']['externalOrderCode'] = $order->getIncrementId();


                    /**
                     * if total is empty and gift card is empty, then probably be a coupon code discount
                     *
                     * hence we can approve the order at once, since no payment method available
                     */
                    $ordersData['SalesOrderDetail']['status'] = self::API_ORDER_INITIAL_STATUS;

                    if ($totals['discount']) {

                        $ordersData['SalesOrderDetail']['totalDiscountAmount'] = $totals['discount'];
                        $ordersData['SalesOrderDetail']['totalValue'] = -$totals['discount'];
                    }

                    $orderItems = $order->getAllItems();
                    $nooforder = 0;
                    $isgiftcard = false;
                    foreach ($orderItems as $item) {
                        ++$nooforder;
                        $parentId = $item->getParentItemId();

                        if (!isset($parentId)) {
                            $giftCardSku = $item->getSku();
                            if (strtoupper(substr($giftCardSku, 0, 8) == 'GIFTCARD'))
                                $isgiftcard = true;
                            $orderLine = array();

                            $orderLine['locationRef'] = $locationRef;
                            $orderLine['sellcodeCode'] = $item->getSku();
                            $orderLine['orderQuantity'] = $item->getQtyOrdered();
                            $itemIncTax = $item->getBasePriceInclTax();
                            $orderLine['listUnitPrice'] = $item->getBasePriceInclTax();
                            if ($itemDiscount = $item->getDiscountAmount()) {

                                $orderLine['listUnitPrice'] = $item->getBasePriceInclTax();
                                $perItemDiscount = $itemDiscount / $item->getQtyOrdered();
                                $itemIncTax = ($item->getBasePriceInclTax() - $perItemDiscount);
//                            $itemIncTax = $itemExclTax+$itemExclTax*($item->getTaxPercent()/100);
                            }

                            $orderLine['unitPrice'] = $itemIncTax;
                            $ordersData['SalesOrderLines'][] = $orderLine;
                        }
                    }

                    $isgiftwrap = false;

                    $giftWrapPrice = $order->getGwItemsPrice() + $order->getGwPrice();
                    if (!empty($giftWrapPrice)) {

                        if ($paymentConfigs && !empty($giftWrapCode)) {

                            $orderLine = array();
                            $orderLine['locationRef'] = $locationRef;
                            $orderLine['sellcodeCode'] = $giftWrapCode;
                            //$giftWrapPrice = $totals['giftwrapping'];
                            $orderLine['orderQuantity'] = round($giftWrapPrice / $giftWrapunitprice);
                            $orderLine['listUnitPrice'] = $giftWrapunitprice;
                            $orderLine['unitPrice'] = $giftWrapunitprice;
                            $isgiftwrap = true;
                            $ordersData['SalesOrderLines'][] = $orderLine;
                        } else {
                            $this->_log("Valid RD code not found for giftcard", Zend_Log::ERR);
                        }
                    }

                    if ($totals['shipping']) {

                        if ($shippingConfig && !empty($shippingConfig[$order->getShippingMethod()]['rd_code'])) {
                            //$deliveryAddress=$shippingConfig[$order->getShippingMethod()]['rd_code'];
                            $orderLine = array();
                            $orderLine['locationRef'] = $locationRef;
                            $shippingmethod = $shippingConfig[$order->getShippingMethod()]['rd_code'];
                            if ($this->getStoreId() == '1081') {
                                If ($shippingmethod == 'POSTQUICK') {
                                    $orderLine['sellcodeCode'] = 'POSTQUICK';
                                } elseif ($shippingmethod == 'CLICKNCOLLECT') {
                                    $orderLine['sellcodeCode'] = 'CLICKNCOLLECT';
                                }elseif ($shippingmethod == 'POSTAGEAU4') {
                                    $orderLine['sellcodeCode'] = 'POSTAGEAU4';
                                } elseif ($shippingmethod == 'POSTAGEAU7') {
                                    $orderLine['sellcodeCode'] = 'POSTAGEAU7';
                                } elseif (($isgiftcard == true) && ($nooforder == 2) && ($isgiftwrap == false)) {
                                    $orderLine['sellcodeCode'] = 'POSTAGEAU';
                                } elseif ($isPObox == true) {
                                    $orderLine['sellcodeCode'] = 'POSTAGEAU3';
                                } elseif ($isGPobox == true) {
                                    $orderLine['sellcodeCode'] = 'POSTAGEAU3';
                                } elseif ($isgiftwrap == true) {
                                    $orderLine['sellcodeCode'] = 'POSTAGEAU2';
                                } elseif ($shippingmethod == 'POSTAGEAU') {
                                    $orderLine['sellcodeCode'] = 'POSTAGEAU1';
                                } else {
                                    $orderLine['sellcodeCode'] = $shippingConfig[$order->getShippingMethod()]['rd_code'];
                                }
                            } else {
                                $orderLine['sellcodeCode'] = $shippingConfig[$order->getShippingMethod()]['rd_code'];
                            }
                            $orderLine['orderQuantity'] = 1;
                            $orderLine['listUnitPrice'] = $totals['shipping']['value'];
                            $orderLine['unitPrice'] = $totals['shipping']['value'];

                            $statusHistory = $order->getStatusHistoryCollection();
                            $deliveryInstructions = "";
                            $nocomments = 1;
                            if ($statusHistory) {
                                foreach ($statusHistory->getItems() as $historyItem) {
                                    //if ($historyItem->getComment() && $nocomments == 1)
                                    //Mage::log($historyItem->getComment().'/n', null, 'history.log');
                                     if (strpos($historyItem->getComment(),"authority-to-leave") !== false)
                                        $deliveryInstructions .= $historyItem->getComment() . " ";
                                    if (strpos($historyItem->getComment(),"signature-required") !== false)
                                        $deliveryInstructions .= $historyItem->getComment() . " ";
                                    $nocomments++;
                                }
                            }
                            $ordersData['SalesOrderLines'][] = $orderLine;
                            $ordersData['SalesOrderDetail']['deliveryInstructions'] = substr($deliveryInstructions, 0, 255);
                        } else {

                            $this->_log("Valid RD code not found for {$order->getShippingMethod()} shipping method", Zend_Log::ERR);
                        }

                    }

                    // eBay store code.
                    $method = $order->getPayment()->getMethodInstance();
                    if ($method->getCode() == 'purchaseorder' && $method->getConfigData('hidden'))
                        $ordersData['storeCode'] = '1085';

                    $orderSubmitDetails = $this->_submitOrder($ordersData);

                    if ($orderSubmitDetails) {

                        $this->customAttributeUpdate($order->getEntityId(),
                            array('rd_order_code' => $orderSubmitDetails->salesorderCode));

                        $ordersCurrency = $order->getOrderCurrencyCode();

                        $payment = $order->getPayment();

                        if ($paymentConfigs) {

                            if ($payment) {

                                $paymentType = null;

                                /**
                                 * Because RD need specific card information, had to deviate form generic pattern
                                 */
                                if ($payment->getMethod() == 'anz_egate' || $payment->getMethod() == 'ccsave') {

                                    $paymentCode = $payment->getMethod() . $payment->getCcType();

                                    if (!empty($paymentConfigs[$paymentCode]['rd_code'])) {

                                        $paymentType = $paymentConfigs[$paymentCode]['rd_code'];
                                    } else {

                                        $this->_log("Valid RD code not found for {$payment->getMethod()} {$payment->getCardType()} payment method", Zend_Log::ERR);
                                    }

                                } else {

                                    if (!empty($paymentConfigs[$payment->getMethod()]['rd_code'])) {

                                        $paymentType = $paymentConfigs[$payment->getMethod()]['rd_code'];
                                    } else {

                                        $this->_log("Valid RD code not found for {$payment->getMethod()} payment method", Zend_Log::ERR);
                                    }
                                }

                                if (!empty($paymentType)) {

                                    $confirmData = array('ConfirmationDetail', 'PaymentDetails');
                                    $confirmData['ConfirmationDetail']['salesorderCode'] = $orderSubmitDetails->salesorderCode;
                                    $confirmData['ConfirmationDetail']['action'] = 'Approve';
                                    $confirmData['ConfirmationDetail']['userCode'] = 1;

                                    $transactionId = ($payment->getLastTransId()) ? $payment->getLastTransId() : $payment->getId();

                                    $confirmData['PaymentDetails'] = array(array('paymentType' => $paymentType,
                                        'paymentReferenceNumber' => $transactionId,
                                        'paymentAmount' => $payment->getAmountOrdered(),
                                        'currencyCode' => $ordersCurrency,
                                        'paymentResultCode' => 0,
                                        'paymentResponseMessage' => 'Approved'));
                                } else {

                                    $this->_log("Valid RD code not found for {$payment->getMethod()} payment method", Zend_Log::ERR);
                                }
                            }

                            if ($totals['giftcard']) {

                                if (is_null($confirmData)) {
                                    $confirmData = array('ConfirmationDetail', 'PaymentDetails');
                                    $confirmData['ConfirmationDetail']['salesorderCode'] = $orderSubmitDetails->salesorderCode;
                                    $confirmData['ConfirmationDetail']['action'] = 'Approve';
                                    $confirmData['ConfirmationDetail']['userCode'] = 1;
                                }

                                foreach ($totals['giftcard'] as $giftCard) {

                                    if (!empty($paymentConfigs[$giftCard['t']]['rd_code'])) {

                                        $confirmData['PaymentDetails'][] = array('paymentType' => $paymentConfigs[$giftCard['t']]['rd_code'],
                                            'paymentReferenceNumber' => $giftCard['c'],
                                            'paymentAmount' => $giftCard['a'],
                                            'currencyCode' => $ordersCurrency,
                                            'paymentResultCode' => 0,
                                            'paymentResponseMessage' => 'Approved');

                                    } else {
                                        $this->_log("Valid RD code not found for {$giftCard['t']} Giftcard payment method", Zend_Log::ERR);
                                    }
                                }
                            }

                            if (!is_null($confirmData))
                                $this->_salesOrderFinalise($confirmData);
                        } else {
                            $this->_log("RD codes not found for payment methods", Zend_Log::ERR);
                        }
                    }
                }
        } catch (SoapFault $fault) {

            $this->_log(array('SOAP Fault', $fault->faultstring));
        } catch (Exception $e) {

            if (isset($ordersData)) {

                Mage::log($e->getMessage(), null, 'RD_Order_Err.log');
                Mage::log($ordersData, null, 'RD_Order_Err.log');
            }

            if (isset($confirmData)) {
                Mage::log($confirmData, null, 'RD_Order_Err.log');
            }

            $this->_log($e->getMessage(), Zend_Log::ERR);
        }
    }

    private function _updateItemStock($entityId, $qty, $isInStock)
    {

        $stockObj = Mage::getModel('cataloginventory/stock_item')->load($entityId);
        $stockObj->setQty($qty);
        $stockObj->setIsInStock($isInStock);
        $stockObj->save();
    }


    /**
     * @todo : remove the code comment.(We may not able to check without the comments)
     *
     * @param $entityId
     * @param $productName
     * @param $sellCode
     * @param $webSiteId
     * @return bool
     */
    public function getProductStock($orderItems, $webSiteId, $ajaxHeader)
    {
        $stockOutProducts = null;
        try {

            $params = new SimpleXMLElement(self::XML_ROOT_NODE);
            $items = array();

            foreach ($orderItems as $k => $item) {
                $items[$item['sku']] = array('product_id' => $k, 'qty' => $item['qty'], 'name' => $item['name']);

                $details = $params->addChild('ItemDetailsGet');
                $details->addChild('itemReference', $item['sku']);
                $details->addChild('supplychannelCode', $this->getSupplyChannelId($webSiteId));
                $details->addChild('storeCode', $this->getStoreId($webSiteId));

            }

            $result = $this->getConnectionModel()->getResult('BulkItemDetailsGet', $params);
            $responsesXml = (array)$result;


            if (!empty($responsesXml['ItemDetailsGetResponse'])) {

                $itemDetailResponse = $responsesXml['ItemDetailsGetResponse'];

                if (is_array($itemDetailResponse)) {

                    foreach ($itemDetailResponse as $itemDetailXml) {

                        if ($itemDetail = $itemDetailXml->ItemDetail) {

                            $itemId = (string)$itemDetail->sellcodeCode;

                            if (!$itemDetail->quantityAvailable || $itemDetail->stockStatus == 'Out of stock') {

                                if (isset($items[$itemId])) {
                                    $stockOutProducts .= "<br/>" . $items[$itemId]['name'];
                                }
                            } elseif ($qty = ((int)$itemDetail->quantityAvailable) < $items[$itemId]['qty']) {

                                $stockOutProducts .= "<br/>" . $items[$itemId]['name'];
                            }
                        }
                    }
                } elseif (is_object($itemDetailResponse)) {

                    if ($itemDetail = $itemDetailResponse->ItemDetail) {

                        $itemId = (string)$itemDetail->sellcodeCode;
                        $qty = (int)$itemDetail->quantityAvailable;

                        if (!$qty || $itemDetail->stockStatus == 'Out of stock') {
                            if (isset($items[$itemId])) {
                                $stockOutProducts .= "<br/>" . $items[$itemId]['name'];
                            }
                        } else if ($qty < $items[$itemId]['qty']) {
                            $stockOutProducts .= "<br/>" . $items[$itemId]['name'];
                        }
                    }
                }
            }


        } catch (SoapFault $fault) {

            $this->_log(array('SOAP Fault', $fault->faultstring));
        } catch (Exception $e) {

            Mage::logException($e);
        }

        if (!empty($stockOutProducts)) {
            Mage::getSingleton('checkout/session')->addError("Requested quantities for following products cannot be fulfilled. $stockOutProducts");
            $this->getQuote()->setHasError(true);
        }
    }


    public function customAttributeUpdate($entityId, $data)
    {
        try {

            $resource = Mage::getSingleton('core/resource');
            $write = $resource->getConnection('core_write');

            $write->update($resource->getTableName('sales/order'), $data, "entity_id = {$entityId}");
        } catch (Exception $e) {
            $this->_log($e->getMessage(), Zend_Log::ERR);
        }
    }



    /**
     * ******************* Test Section ******************************
     *
     * Entry point for product information download.
     */

    /**
     * @todo incomplete function return an error
     *
     *  [exceptionType] => DataAccessWarningException
     * [errorNumber] => 2812
     * [errorMessage] => Could not find stored procedure 'up_saleshistory_sellist'.
     * [errorSource] => RetailDirections.DataAccess.SalesOrderServices
     *
     * Get customer order history
     *
     * @param $id
     *
     * @return null
     */
    public function getCustomerSalesHistory($customerId, $dateFrom, $dateTo)
    {
        if (!empty($customerId)) {

            $params = new SimpleXMLElement(self::XML_ROOT_NODE);
            $sale = $params->addChild('SalesHistoryFind');

            $sale->addChild('customerId', $customerId);
            $sale->addChild('locationCode', $this->getStoreId());
            $sale->addChild('dateFrom', $dateFrom);
            $sale->addChild('dateTo', $dateTo);

            $result = $this->getConnectionModel()->getResult(self::API_METHOD_ORDER_HISTORY, $params);
            return $result;

        }

        return null;
    }

    /**
     * Freight
     * @return bool|SimpleXMLElement
     */
    public function getFreightMethodsCosts()
    {

        $params = new SimpleXMLElement(self::XML_ROOT_NODE);
        $sale = $params->addChild('GetFreightMethodsCosts');

        $sale->addChild('storeCode', '1081');
        $sale->addChild('countryCode', 'AU');

        $result = $this->getConnectionModel()->getResult('GetFreightMethodsCosts', $params, 'GetFreightMethodsCosts');
        return $result;
    }



//    public function getOrderDetails()
//    {
//        try
//        {
//            $this->_getOrderDetails('000127', 'Approve');
//        }
//        catch (Exception $e)
//        {
//            print_r($e->getMessage());
//            $this->_log($e->getMessage(), Zend_Log::ERR);
//        }
//    }

    public function createOrders2()
    {
        echo 1;
        die();
        try {
            $elements = array('SalesOrderDetail', 'SalesOrderLines');
            $elements['SalesOrderDetail']['customerId'] = '108100020740';
            $elements['SalesOrderDetail']["storeCode"] = '1';
//            $elements['SalesOrderDetail']["customerTypeCode"] = 'STAFF';
            $elements['SalesOrderDetail']["deliveryInstructions"] = 'Leave at front desk';

            $elements['SalesOrderLines']['SalesOrderLine']['locationRef'] = 'Home Prasad';
            $elements['SalesOrderLines']['SalesOrderLine']["sellcodeCode"] = '9341370060838';
            $elements['SalesOrderLines']['SalesOrderLine']["orderQuantity"] = '1';
            $elements['SalesOrderLines']['SalesOrderLine']["unitPrice"] = '2.2';
            $elements['SalesOrderLines']['SalesOrderLine']["customerDiscountVoucher"] = '2222222222';
            $elements['SalesOrderLines']['SalesOrderLine']["deliverAfterDate"] = '2012-03-08T15:09:54.3936615+11:00';


            $this->_submitOrder($elements);
        } catch (Exception $e) {
            print_r($e->getMessage());
            $this->_log($e->getMessage(), Zend_Log::ERR);
        }
    }



    public function confirmOrders()
    {
        try {
            $elements = array('ConfirmationDetail', 'PaymentDetails');
            $elements['ConfirmationDetail']['salesorderCode'] = '108101028755';
            $elements['ConfirmationDetail']["action"] = 'Approve';
            $elements['ConfirmationDetail']["paymentReferenceNumber"] = 'XXX0123456789XXX';
            $elements['SalesOrderDetail']["userCode"] = '1';

            $elements['PaymentDetails']['PaymentDetail']['paymentType'] = 'PAYPAL';
            $elements['PaymentDetails']['PaymentDetail']["paymentReferenceNumber"] = 'XXX0123456789XXX';
            $elements['PaymentDetails']['PaymentDetail']["paymentAmount"] = '11';
            $elements['PaymentDetails']['PaymentDetail']["currencyCode"] = 'AUD';
            $elements['PaymentDetails']['PaymentDetail']["paymentResultCode"] = '0';
            $elements['PaymentDetails']['PaymentDetail']["paymentResponseMessage"] = 'Approved';


            $this->_salesOrderFinalise($elements);
        } catch (Exception $e) {
            print_r($e->getMessage());
            $this->_log($e->getMessage(), Zend_Log::ERR);
        }
    }
}
