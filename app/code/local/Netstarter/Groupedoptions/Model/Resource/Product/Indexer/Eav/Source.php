<?php
class Netstarter_Groupedoptions_Model_Resource_Product_Indexer_Eav_Source
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Eav_Source
    //extends Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source
{

    protected function _prepareRelationIndex($parentIds = null)
    {
        $write      = $this->_getWriteAdapter();
        $idxTable   = $this->getIdxTable();

        $select = $write->select()
            ->from(array('l' => $this->getTable('catalog/product_relation')), 'parent_id')
            ->join(
                array('cs' => $this->getTable('core/store')),
                '',
                array())
            ->join(
                array('i' => $idxTable),
                'l.child_id = i.entity_id AND cs.store_id = i.store_id',
                array('attribute_id', 'store_id', 'value'))
            ->group(array(
                'l.parent_id', 'i.attribute_id', 'i.store_id', 'i.value'
            ));
        if (!is_null($parentIds)) {
            $select->where('l.parent_id IN(?)', $parentIds);
        }

        /**
         * Add additional external limitation
         */
        Mage::dispatchEvent('prepare_catalog_product_index_select', array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('l.parent_id'),
            'website_field' => new Zend_Db_Expr('cs.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id')
        ));

        $query = $write->insertFromSelect($select, $idxTable, array(), Varien_Db_Adapter_Interface::INSERT_IGNORE);
        $write->query($query);

        /* BEGIN Brim Grouped-Options Customizations */
        if (Mage::getStoreConfigFlag('groupedoptions/frontend/enable_expanded_layerednav')) {
            /*
                Added better support configurable products in layered navigation.  simple products associated with
                configurable products were not flowing into the grouped products causing grouped products to not be displayed
                when they should be.
             */
            $select->join(
                array('grouped_catalog' => $this->getTable('catalog/product')),
                'l.parent_id = grouped_catalog.entity_id',
                array()
            );
            $select->where("grouped_catalog.type_id = 'grouped'");

            $query = $write->insertFromSelect($select, $idxTable, array(), Varien_Db_Adapter_Interface::INSERT_IGNORE);
            $write->query($query);
        }
        /* END Brim Grouped-Options Customizations */

        return $this;
    }
}
