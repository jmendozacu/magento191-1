<?php
/**
 * Class Filter
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Model_Resource_Filter_Option extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct() {
        $this->_init('bz_navigation/filter_option', 'value_id');
    }
}