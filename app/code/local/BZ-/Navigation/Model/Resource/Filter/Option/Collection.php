<?php
/**
 * Class Collection
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Model_Resource_Filter_Option_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('bz_navigation/filter_option');
    }
}
