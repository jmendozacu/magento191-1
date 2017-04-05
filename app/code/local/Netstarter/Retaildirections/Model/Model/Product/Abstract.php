<?php

/**
 * Abstract for the Product models.
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 *
 * Class Netstarter_Retaildirections_Model_Model_Product_Abstract
 */
abstract class Netstarter_Retaildirections_Model_Model_Product_Abstract extends Netstarter_Retaildirections_Model_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * @return string
     */
    public function getStoreId($website = null)
    {
        if ($website == null)
        {
            $website = $this->_getDefaultWebsiteCode();
        }

        return parent::getStoreId($website);
    }

    /**
     * @return string
     */
    public function getSupplyChannelId($website = null)
    {
        if ($website == null)
        {
            $website = $this->_getDefaultWebsiteCode();
        }

        return parent::getSupplyChannelId($website);
    }
}
