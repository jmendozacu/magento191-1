<?php
class Netstarter_Eway_Model_Resource_Token extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('netstarter_eway/token', 'token_id');
    }
}