<?php


/**
 * Class Netstarter_Cdn_Helper_Data
 * This is used to write common function for cdn module
 */
class Netstarter_Cdn_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Returns the version number
     * @return bool|mixed
     */
    public function getVersionNumber()
    {
        $versionNumber = false;
        if (Mage::getStoreConfig('cdn/settings/active')) {
            $versionNumber = Mage::registry('deployversion');
            if(!$versionNumber){
                $versionNumber = Mage::getStoreConfig('cdn/settings/rev_number');
                Mage::register('deployversion', $versionNumber);
            }
        }
        return $versionNumber;
    }

}


