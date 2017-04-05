<?php
/**
 * @author http://www.netstarter.com.au
 * @licence http://www.netstarter.com.au
 */ 
class Netstarter_FitWizard_Model_Resource_CouponEmail_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('fitwizard/couponEmail');
    }

}