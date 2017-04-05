<?php
/**
 * @author Prasad
 *
 * Class Netstarter_Location_Model_Resource_Postcode
 */
class Netstarter_Location_Model_Resource_Postcode extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('location/postcode', 'id');
    }
}