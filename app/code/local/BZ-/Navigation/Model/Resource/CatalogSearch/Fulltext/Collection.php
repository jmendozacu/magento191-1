<?php
/**
 * Netstarter.com.au
 *
 * PHP Version 5.4
 *
 * @copyright 2014 Netstarter.com.au
 * 
 */ 
class BZ_Navigation_Model_Resource_CatalogSearch_Fulltext_Collection extends Mage_CatalogSearch_Model_Resource_Fulltext_Collection
{

    public function filterMultipleCategories($multiCatId)
    {
        $conditions = array(
            'cat_index_sub.product_id=e.entity_id',
            $this->getConnection()->quoteInto('cat_index_sub.category_id=?', $multiCatId)
        );

        $joinCond = join(' AND ', $conditions);

        $this->getSelect()->join(
            array('cat_index_sub' => $this->getTable('catalog/category_product_index')),
            $joinCond
        );
    }
}