<?php

class Netstarter_Storeorder_Model_Export extends Netstarter_Shelltools_Model_Shared_Abstract
{
    protected $_jobId = 'GENERATE_STORE_ORDER_REPORT';

    protected function _update()
    {
        $this->exportOrders();
    }
    
    public function exportOrders(){
        if(!Mage::helper("netstarter_tbyb")->isEnabled()){
            return true;
        }
        try{
            $this->_log("Starting store order CSV generation");
            
            $products = $this->getProducts();
            $csvString = "Store ID, Purchase Date, Future Payment Date, Magento Order ID, RMS Order ID, Product SKU, Item Color Code, Ordered Store ID, Customer Name, Email address, TBYB Status, Date Cancelled\r\n";
            
            $this->_log(count ($products) . " items found.");
            
            foreach($products as $p){
                $csvString .= sprintf("%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\r\n",
                    $p->getStoreId(),
                    $p->getPurchaseDate(),
                    $p->getFuturePaymentDate(),
                    $p->getOrderId(),
                    $p->getRmsOrderId(),
                    $p->getSimpleProductSku(),
                    $p->getConfigurableProductSku(),
                    $p->getStoreOrderId(),
                    $p->getTbybCustomerName(),
                    $p->getCustomerEmail(),
                    $p->getTbybStatus(),
                    $p->getTbybCancelledAt()
                );
            }
            $this->writeToCSV($csvString);
            
        }catch(Exception $e){
            $this->_log("ERROR : " . $e->getMessage());
        }
    }
    
    private function getProducts(){
        date_default_timezone_set("Australia/Sydney");
        $nowAUStart = date("Y-m-d");
        $nowAUEnd = date("Y-m-d H:i:s");
        $dateDiffAU = strtotime($nowAUEnd) - strtotime($nowAUStart);

        date_default_timezone_set("UTC");
        $nowUTCEnd = date("Y-m-d H:i:s");
        $nowUTCStart = strtotime($nowUTCEnd) - $dateDiffAU;
        $nowUTCStart = date("Y-m-d H:i:s", $nowUTCStart);
        $nowUTCEnd = strtotime($nowUTCEnd) - $dateDiffAU + 86399;
        $nowUTCEnd = date("Y-m-d H:i:s", $nowUTCEnd);
        
//        $orders = Mage::getModel('sales/order')->getCollection()
//                                ->addAttributeToFilter("store_order_id", array('neq' => 'null'))
//                                ->addAttributeToFilter('created_at', array(
//                                    'from' => (strtotime($nowUTCStart) - (60*60*24)),
//                                    'to' => (strtotime($nowUTCEnd) - (60*60*24)),
//                                    'datetime' => true
//                                ))
//                                ->load();

        $orders = Mage::getModel('sales/order')->getCollection()
                                ->addAttributeToFilter("store_order_id", array('neq' => 'null'))
                                ->addAttributeToFilter('main_table.created_at', array(
                                    'from' => (strtotime($nowUTCStart) - (60*60*24)),
                                    'to' => (strtotime($nowUTCEnd) - (60*60*24)),
                                    'datetime' => true
                                ));

        $storeOrderItems = array();
        $cofigurableProducts = array();
        
        $storeModel = Mage::getModel("storeorder/store");
        $tbybStatusArray = Mage::getModel('netstarter_tbyb/status')->getOptionsArray();

        foreach($orders as $order){
            $items = $order->getAllVisibleItems();
            foreach($items as $item){

                $storeOrderItem = new Varien_Object();
                $storeOrderItem->setData("store_id", $order->getStoreOrderId());
                $storeOrderItem->setData("purchase_date", date("Y-m-d",Mage::getModel('core/date')->timestamp($order->getCreatedAt())));
                $storeOrderItem->setData("future_payment_date", $order->getFuturePaymentDate() ? date("Y-m-d",Mage::getModel('core/date')->timestamp($order->getFuturePaymentDate())) : "");
                $storeOrderItem->setData("order_id", $order->getIncrementId());
                $storeOrderItem->setData("rms_order_id", $order->getRdOrderCode());
                $storeOrderItem->setData("product_name", str_replace(",", " ", $item->getName()));
                $storeOrderItem->setData("simple_product_sku", $item->getSku());
                $product = null;
                if(isset($cofigurableProducts[$item->getProductId()])){
                    $product = $cofigurableProducts[$item->getProductId()];
                }else{
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    $cofigurableProducts[$item->getProductId()] = $product;
                }
                if($storeModel->isTryBeforeYouBuyProducts($product->getSku())){
                    $storeOrderItem->setData("configurable_product_sku", $product->getSku());


                    $tbybItem = Mage::getModel('netstarter_tbyb/item')->getCollection()
                        ->addFieldToFilter('order_id', $order->getId())
                        ->addFieldToFilter('order_item_id', $item->getItemId())
                    //Mage::log($tbybItem->getSelect()->assemble());
                        ->getFirstItem();

                    // Set TBYB Curvesence Data
                    //** Joined from netstarter_tbyb/item table */
                    // todo: now $order contains additional data other than for order, should rename it to more meaningful one

                    $storeOrderItem->setData("store_order_id", $order->getData('store_order_id'));
                    $storeOrderItem->setData("customer_email", $order->getData('customer_email'));


                    if ($tbybItem->getId()) {
                        $storeOrderItem->setData("tbyb_customer_name", $tbybItem->getCustomerName());
                        $tbybStatus = array_key_exists($tbybItem->getStatus(), $tbybStatusArray) ? $tbybStatusArray[$tbybItem->getStatus()] : $tbybItem->getStatus();
                        $storeOrderItem->setData("tbyb_status", $tbybStatus);

                        $storeOrderItem->setData("tbyb_cancelled_at", $tbybItem->getCancelledAt());
                    }
                    $storeOrderItems[] = $storeOrderItem;
                }
            }
        }

        return $storeOrderItems;
    }
    
    private function writeToCSV($s){
        $path = dirname(Mage::getRoot()) . '/var/curvesence';
        if(!is_dir($path)){
            mkdir($path);
        }
        $currentTimestamp = Mage::getModel('core/date')->timestamp(time()); //Magento's timestamp function makes a usage of timezone and converts it to timestamp
        $path .= "/" . date('Y-m-d', $currentTimestamp) . ".csv";
        
        $f = fopen($path, "w");
        fwrite($f, $s);
        fclose($f);
        $this->_log("CSV file has been successfully exported to " . $path);
    }
}