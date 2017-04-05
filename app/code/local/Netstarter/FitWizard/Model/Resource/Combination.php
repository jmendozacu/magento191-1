<?php
/**
 * @author http://www.netstarter.com.au
 * @licence http://www.netstarter.com.au
 */ 
class Netstarter_FitWizard_Model_Resource_Combination extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('fitwizard/net_fitwizard_category', 'brand_id');
    }

}