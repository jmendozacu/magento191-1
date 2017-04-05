<?php
/**
 * Class Filter
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Model_Resource_Filter extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct() {
        $this->_init('bz_navigation/filter', 'filter_id');
    }
}
