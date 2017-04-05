<?php
/**
 * @author Prasad
 *
 * Class Netstarter_Location_Model_Resource_Postcode_Collection
 */
class Netstarter_Location_Model_Resource_Postcode_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('location/postcode');
    }
}