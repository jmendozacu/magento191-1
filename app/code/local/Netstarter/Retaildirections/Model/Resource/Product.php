<?php

/**
 * Class Netstarter_Retaildirections_Model_Resource_Product
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 *
 * Deals with which specific flat row downloaded from RD API.
 * It is than normalized within a flat structure this Model is the CRUD for.
 *
 * Attributes can be repeated along the table, however it is decided to go ahead with this
 * structure at this point as it will make collection retrieval easier and will make information
 * available at hand for each row.
 *
 */
class Netstarter_Retaildirections_Model_Resource_Product extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Use Magento standard unique field configuration validation.
     * Raises an exception in case of not normal data.
     */
    protected function _construct()
    {
        $this->_uniqueFields = array(
            array(
            'field' => array('item_colour_ref', 'item_code', 'sell_code_code'),
            'title' => 'Combination of item_colour_ref, item_code and sell_code_code should be unique'
            )
        );

        $this->_init('netstarter_retaildirections/product', 'model_id');
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        /*
         * Set update time based on Locale settings
         */
        if ($object->hasDataChanges())
        {
            $object->addData(
                array(
                    'updated_at' => $this->formatDate(Mage::app()->getLocale()->date(), true)
                )
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     * Retrieve row ID based on product attributes.
     *
     * @param array $productData
     * @return string
     */
    public function getIdByKeys($productData = array())
    {
        $adapter = $this->_getReadAdapter();

        if (!array_key_exists('sell_code_code', $productData))
        {
            $productData['sell_code_code'] = '';
        }

        $bind    = array(
            'item_colour_ref' => $productData['item_colour_ref'],
            'item_code' => $productData['item_code'],
            'sell_code_code' => $productData['sell_code_code'],
        );

        $select  = $adapter->select()
            ->from($this->getMainTable(), array($this->getIdFieldName()))
            ->where('item_colour_ref = :item_colour_ref')
            ->where('item_code = :item_code')
            ->where('sell_code_code = :sell_code_code');

        $balanceId = $adapter->fetchOne($select, $bind);
        return $balanceId;
    }
}