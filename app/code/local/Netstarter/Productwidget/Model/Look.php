<?php

class Netstarter_Productwidget_Model_Look extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('productwidget/look');
    }

    /**
     * @param $proId]
     *
     * get Complete the Look products from lookbook_link table
     *
     * @return $lookprocollection
     */
    public function getCompleteTheLooksProducts($proId)
    {

        $lookprocollection = $this->getResourceCollection()->addFieldToFilter('product_id',$proId);

        return $lookprocollection;
    }
	
}