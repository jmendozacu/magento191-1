<?php

class Netstarter_Eway_Block_Form_Cc extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
        parent::_construct();
        
        $token = Mage::getModel('netstarter_eway/token')
        ->loadByCustomerId(
            Mage::getSingleton('customer/session')->getCustomer()->getId(),
            Mage::app()->getWebsite()->getId()
        );
        
        if ($token === false)
        {
            $this->setTemplate('payment/form/cc.phtml');
        }
        else
        {
            $this->setTemplate('netstarter/eway/form/existing.phtml');
        }
    }
}