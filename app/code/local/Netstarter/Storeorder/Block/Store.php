<?php

class Netstarter_Storeorder_Block_Store extends Mage_Core_Block_Template
{
    public function validateStore()
    {
        $store = Mage::getSingleton('storeorder/store');
        return $store->validateStore();
    }
}
