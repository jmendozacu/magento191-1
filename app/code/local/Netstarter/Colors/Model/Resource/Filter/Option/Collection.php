<?php
/**
 * Class Collection
 *
 * @author bzhang@netstarter.com.au
 */
class Netstarter_Colors_Model_Resource_Filter_Option_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('colors/filter_option');
    }
}
