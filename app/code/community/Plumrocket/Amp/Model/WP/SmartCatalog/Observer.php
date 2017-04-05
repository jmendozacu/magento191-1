<?php

class Plumrocket_Amp_Model_WP_SmartCatalog_Observer extends WP_SmartCatalog_Model_Observer
{
    public function observeLayoutHandleInitialization(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('pramp');
        if ($helper->isAmpRequest()) {
            return;
        }

        parent::observeLayoutHandleInitialization($observer);
    }
}
