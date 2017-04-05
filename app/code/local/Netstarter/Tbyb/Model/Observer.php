<?php

class Netstarter_Tbyb_Model_Observer
{
    
    public function deleteGiftcardCoupon(Varien_Event_Observer $observer)
    {
        if (!Mage::helper("netstarter_tbyb")->isEnabled()) { return true; }
        
        $storeorder = Mage::getSingleton('storeorder/store');
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        //
        if($storeorder->cartHasTbybProducts()){
            $quote->setData('coupon_code','')->save();
            $totalsCart = $quote->getTotals();
            foreach($totalsCart as $k=>$total){
                switch($k){
                    case 'giftcardaccount':
                        $giftCards = $total->getGiftCards();
                        foreach($giftCards as $g){
                            Mage::getModel('enterprise_giftcardaccount/giftcardaccount')->loadByCode($g["c"])->removeFromCart();
                        }
                        break;
                    case 'giftwrapping':
                        break;
                }
            }
        }
        
    }
    
    public function activatePaymentMethod(Varien_Event_Observer $observer){
        $storeorder = Mage::getSingleton('storeorder/store');
        $event = $observer->getEvent();
        $method = $event->getMethodInstance();
        $result = $event->getResult();
        $path = 'payment/'. $method->getCode() .'/active';
        
        if (!Mage::helper("netstarter_tbyb")->isEnabled()) { 
            if($method->getCode() == Mage::getStoreConfig(Netstarter_Tbyb_Model_Source_Payment::XML_PATH))
                $result->isAvailable = false;
            return true; 
        }
        
        if($method->canUseCheckout() && Mage::getStoreConfig($path, Mage::app()->getStore()->getStoreId())){
            $hasTbybProducts = $storeorder->cartHasTbybProducts();
            if($method->getCode() == Mage::getStoreConfig(Netstarter_Tbyb_Model_Source_Payment::XML_PATH))
                $result->isAvailable = $hasTbybProducts;
            else
                $result->isAvailable = !$hasTbybProducts;
            
        }
    }
    
    public function saveFuturePayments($observer){
        if (!Mage::helper("netstarter_tbyb")->isEnabled()) { return true; }
        
        try{
            $incrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
            $items = $order->getAllItems();
            $cofigurableProducts = array();
            $storeorder = Mage::getSingleton('storeorder/store');
            $futurePaymentDate = "";
            foreach($items as $item){
                if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
                    $product = null;
                    if(isset($cofigurableProducts[$item->getProductId()])){
                        $product = $cofigurableProducts[$item->getProductId()];
                    }else{
                        $product = Mage::getModel('catalog/product')->load($item->getProductId());
                        $cofigurableProducts[$item->getProductId()] = $product;
                    }

                    if($storeorder->isTryBeforeYouBuyProducts($product->getSku())){
                        
                        $tbybItem = Mage::getModel("netstarter_tbyb/item");
                        
                        $tbybItem->setOrderId($order->getEntityId());
                        $tbybItem->setIncrementId($incrementId);
                        $tbybItem->setCustomerId($order->getCustomerId());
                        $tbybItem->setCustomerName($order->getCustomerFirstname() . " " . $order->getCustomerLastname());
                        $tbybItem->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                        $tbybItem->setCurrencyCode($order->getOrderCurrencyCode());

                        $tbybItem->setOrderItemId($item->getItemId());
                        $tbybItem->setProductId($item->getProductId());
                        $tbybItem->setSku($item->getSku());
                        $tbybItem->setItemColourRef($product->getSku());
                        $tbybItem->setPrice(($item->getPriceInclTax()*$item->getQtyOrdered())-$item->getDiscountAmount());
                        $tbybItem->setQty($item->getQtyOrdered());
                        $tbybItem->setCreatedAt($order->getCreatedAt());
                        
                        $futurePaymentDate = strtotime("+" . intval(Mage::getStoreConfig("curvesence/tbyb/nooffuturepaymentday")) + 1 . " day", strtotime($order->getCreatedAt()));
                        $tbybItem->setFuturePaymentDate($futurePaymentDate);
                        
                        $tbybItem->setStatus(Netstarter_Tbyb_Model_Status::STATUS_TOBECHARGED);
                        $tbybItem->save();

                    }
                }
            }
            if($futurePaymentDate){
                $order->setFuturePaymentDate(strtotime("+" . intval(Mage::getStoreConfig("curvesence/tbyb/nooffuturepaymentday")) + 1 . " day", strtotime($order->getCreatedAt())));
                $order->save();
            }
            
        }catch(Exception $e){
            Mage::log($e->getMessage());
        }
    }
}
