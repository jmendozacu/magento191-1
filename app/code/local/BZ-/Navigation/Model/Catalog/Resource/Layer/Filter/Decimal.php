<?php

/**
 * Class Decimal
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Model_Catalog_Resource_Layer_Filter_Decimal extends Mage_Catalog_Model_Resource_Layer_Filter_Decimal
{  
    /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Decimal $filter
     * @param float $range
     * @param int $index
     * @return Mage_Catalog_Model_Resource_Layer_Filter_Decimal
     */
    public function applyFilterToCollection($filter, $from, $to)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $attribute  = $filter->getAttributeModel();
        $connection = $this->_getReadAdapter();
        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId())
        );

        $collection->getSelect()->join(
            array($tableAlias => $this->getMainTable()),
            implode(' AND ', $conditions),
            array()
        );
        
        $collection->getSelect()
            ->where("{$tableAlias}.value >= ?", (int)$from)
            ->where("{$tableAlias}.value < ?", (int)$to);
        return $this;
    }
    
    public function getOrgMaxMin($filter){
        $adapter = $this->_getReadAdapter();
        $collection = $filter->getLayer()->getCurrentCategory()->getProductCollection();
        $select = clone $collection->getSelect();
        $attributeId = $filter->getAttributeModel()->getId();
        $storeId = $collection->getStoreId();
        $select->join(
                array('decimal_index' => $this->getMainTable()), 'e.entity_id = decimal_index.entity_id' .
                ' AND ' . $this->_getReadAdapter()->quoteInto('decimal_index.attribute_id = ?', $attributeId) .
                ' AND ' . $this->_getReadAdapter()->quoteInto('decimal_index.store_id = ?', $storeId), array()
        );
        $select->columns(array(
            'org_min_value' => new Zend_Db_Expr('MIN(decimal_index.value)'),
            'org_max_value' => new Zend_Db_Expr('MAX(decimal_index.value)'),
        ));
        $result = $adapter->fetchRow($select);
        return array($result['org_min_value'], $result['org_max_value']);
    }
}
