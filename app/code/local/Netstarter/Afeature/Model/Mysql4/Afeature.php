<?php
 
class Netstarter_Afeature_Model_Mysql4_Afeature extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('afeature/afeature', 'afeature_id');
    }
}