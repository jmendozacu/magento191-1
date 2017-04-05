<?php
class Netstarter_Tbyb_Model_Resource_Item extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('netstarter_tbyb/item', 'item_id');
    }
}