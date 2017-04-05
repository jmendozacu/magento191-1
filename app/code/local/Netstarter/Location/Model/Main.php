<?php
/**
 * @author Prasad
 *
 * Class Netstarter_Location_Model_Main
 */
class Netstarter_Location_Model_Main extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('location/main');
    }

    public function saveInfo($data)
    {
        $this->_getResource()->saveInfo($data);
    }

    public function loadInfo()
    {
        return $this->_getResource()->loadInfo($this);
    }

    public function checkIdentifier($identifier, $store, $isActive)
    {
        return $this->_getResource()->checkIdentifier($identifier, $store, $isActive);
    }

    public function deleteInfo()
    {
        return $this->_getResource()->deleteInfo($this);
    }
}
