<?php
 
class Netstarter_Afeature_Model_Afeature extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('afeature/afeature');
    }
	
	
}