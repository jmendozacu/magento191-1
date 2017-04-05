<?php

/**
 * Class Netstarter_Retaildirections_Model_Resource_Product_Collection
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 *
 * Retrieve a list of downloaded products to be translated into Magento.
 *
 */
class Netstarter_Retaildirections_Model_Resource_Product_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize collection
     *
     */
    public function _construct()
    {
        $this->_init('netstarter_retaildirections/product');
    }

    /**
     * @param string $code
     * @return Netstarter_Retaildirections_Model_Resource_Product_Collection
     */
    public function getBaseItemCodesToTranslate($code = '')
    {
        // Return a new object, and doesn't change the data of this one.
        $_newCollection = clone $this;

        // Attribute which products will be divisible into.
        // Currently is the color level. This level is the level
        // the configurable will be created in Magento (e.g. colour level, master level).
        if ($code == '')
        {
            $code = $this->getResource()->getIdFieldName();
        }

        /*
         * Retrieve products that were retrieved from the API more recently than
         * they got translated.
         *
         * These products will be translated into Magento.
         */
        $_newCollection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns($code)
            ->where('retrieved_from_api_at > translated_at')
            ->orWhere('translated_at IS NULL')
            ->group(array($code));

        return $_newCollection;
    }

    /**
     * @return Netstarter_Retaildirections_Model_Resource_Product_Collection
     */
    public function getItemCodesToTranslateByItemCode()
    {
        return $this->getBaseItemCodesToTranslate('item_code');
    }

    /**
     * @return Netstarter_Retaildirections_Model_Resource_Product_Collection
     */
    public function getItemCodesToTranslateByItemColourRef()
    {
        return $this->getBaseItemCodesToTranslate('item_colour_ref');
    }
}
