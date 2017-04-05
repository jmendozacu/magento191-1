<?php

class BZ_Solr_Model_Resource_Index extends Mage_CatalogSearch_Model_Resource_Fulltext
{
    /**
     * Return array of price data per customer and website by products
     * @param   null|array $productIds
     * @return  array
     */
    protected function _getCatalogProductPriceData($productIds = null)
    {
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()
            ->from($this->getTable('catalog/product_index_price'),
                array('entity_id', 'customer_group_id', 'website_id', 'min_price'));
        if ($productIds) {
            $select->where('entity_id IN (?)', $productIds);
        }
        $result = array();
        foreach ($adapter->fetchAll($select) as $row) {
            $result[$row['website_id']][$row['entity_id']][$row['customer_group_id']] = round($row['min_price'], 2);
        }
        return $result;
    }

    /**
     * Retrieve price data for product
     * @param   $productIds
     * @param   $storeId
     * @return  array
     */
    public function getPriceIndexData($productIds, $storeId)
    {
        $priceProductsIndexData = $this->_getCatalogProductPriceData($productIds);
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        if (!isset($priceProductsIndexData[$websiteId])) {
            return array();
        }
        return $priceProductsIndexData[$websiteId];
    }

    /**
     * Prepare system index data for products.
     * @param   int $storeId
     * @param   int|array|null $productIds
     * @return  array
     */
    public function getCategoryProductIndexData($storeId = null, $productIds = null)
    {
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()
            ->from(
                array($this->getTable('catalog/category_product_index')),
                array(
                    'category_id',
                    'product_id',
                    'position',
                    'store_id'
                )
            )
            ->where('store_id = ?', $storeId);
        if ($productIds) {
            $select->where('product_id IN (?)', $productIds);
        }
        $result = array();
        foreach ($adapter->fetchAll($select) as $row) {
            $result[$row['product_id']][$row['category_id']] = $row['position'];
        }
        return $result;
    }

    /**
     * Retrieve moved categories product ids
     * @param   int $categoryId
     * @return  array
     */
    public function getMovedCategoryProductIds($categoryId)
    {
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()
            ->distinct()
            ->from(
                array('c_p' => $this->getTable('catalog/category_product')),
                array('product_id')
            )
            ->join(
                array('c_e' => $this->getTable('catalog/category')),
                'c_p.category_id = c_e.entity_id',
                array()
            )
            ->where($adapter->quoteInto('c_e.path LIKE ?', '%/' . $categoryId . '/%'))
            ->orWhere('c_p.category_id = ?', $categoryId);
        return $adapter->fetchCol($select);
    }
}
