<?php
class Netstarter_Eway_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfigFlag("payment/netstarter_eway_rapid31/active");
    }
}
