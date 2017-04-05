<?php

class Netstarter_Checkout_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isStoreOrder()
    {
        if (Mage::helper('core')->isModuleEnabled('Netstarter_Storeorder'))
        {
            $storeModel = Mage::getModel("storeorder/store");
            
            if (is_object($storeModel))
            {
                if ($storeModel->validateStore())
                {
                    return true;
                }
            }
        }
        
        return false;
    }
}