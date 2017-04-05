<?php

class Netstarter_Productwidget_Model_Mysql4_Look extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('productwidget/look', 'link_id');
    }
}