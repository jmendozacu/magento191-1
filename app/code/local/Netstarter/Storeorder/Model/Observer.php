<?php

class Netstarter_Storeorder_Model_Observer{
    public function saveStoreOrderToSession($observer){
        try{
            $store = Mage::getSingleton('storeorder/store');
            if($store->validateStore()){
                $storeOrderId = $observer->getEvent()->getRequest()->getPost('store_order_id', "");
                $session = Mage::getSingleton('checkout/session');
                $session->setData('store_order_id', $storeOrderId);
            }
        }catch(Exception $e){
            Mage::log($e->getMessage());
        }
    }
    public function saveStoreOrderToOrder($observer){
        try{
            $store = Mage::getSingleton('storeorder/store');
            if($store->validateStore()){
                $session = Mage::getSingleton('checkout/session');
                $storeOrderId = $session->getData('store_order_id');

                if($storeOrderId){
                    $observer->getEvent()->getOrder()->setStoreOrderId($storeOrderId);
                }
            }
        }catch(Exception $e){
            Mage::log($e->getMessage());
        }
    }
}