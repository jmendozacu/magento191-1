<?php

/**
 * Class Filteroption
 *
 * @author bzhang@netstarter.com.au
 */
class Netstarter_Colors_Model_Filter_Option extends Mage_Core_Model_Abstract
{
    public function _construct() {
        //parent::_construct();
        $this->_init('colors/filter_option');
    }
    
    public function loadByOptionId($option_id){
        $model = $this->load($option_id,'option_id');
        return $model;
    }
}
