<?php
class Netstarter_Eway_Model_Resource_Token_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('netstarter_eway/token');
    }
}