<?php
/**
 * Product in backend category grid
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 *
 * Class Netstarter_Retaildirections_Block_Adminhtml_Catalog_Category_Tab_Product
 */
class Netstarter_Retaildirections_Block_Adminhtml_Catalog_Category_Tab_Product extends Mage_Adminhtml_Block_Catalog_Category_Tab_Product
{
    /**
     *
     * Add the RD category to be manageable in the category page.
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter('rd_category', array(
            'header'    => Mage::helper('catalog')->__('RD Category'),
            'index'     => 'rd_category'
        ), 'sku');

        return parent::_prepareColumns();
    }

    /**
     *
     * Add the RD category attribute to the collection to be filtered.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $return = parent::_prepareCollection();

        $this->getCollection()->clear();
        $this->getCollection()->addAttributeToSelect('rd_category');

        return $return;
    }
}