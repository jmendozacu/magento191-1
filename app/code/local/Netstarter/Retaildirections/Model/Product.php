<?php

/**
 * Class Netstarter_Retaildirections_Model_Product
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 *
 * Flat product, retrieved from the RD API, and normalized into a table row per sell_code.
 *
 */
class Netstarter_Retaildirections_Model_Product extends Mage_Core_Model_Abstract
{
    /**
     * Basic Magento ActiveRecord initialization
     */
    protected function _construct()
    {
        $this->_init('netstarter_retaildirections/product');
    }

    /**
     * Get the row ID based on product data (keys).
     *
     * @param array $productData
     * @return mixed
     */
    public function getIdByKeys($productData = array())
    {
        return $this->getResource()->getIdByKeys($productData);
    }

    /**
     * For standard clearInstance() call and model reuse.
     *
     * @return $this
     */
    protected function _clearReferences()
    {
        foreach ($this->_data as $data){
            if (is_object($data) && method_exists($data, 'reset')){
                $data->reset();
            }
            if (is_object($data) && method_exists($data, 'clearInstance')){
                $data->clearInstance();
            }
        }
        return $this;
    }

    /**
     * For standard clearInstance() call and model reuse.
     *
     * @return $this
     */
    protected function _clearData()
    {
        $this->setData(array());
        return $this;
    }
}