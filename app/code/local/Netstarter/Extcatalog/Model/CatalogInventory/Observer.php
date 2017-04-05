<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 10/9/13
 * Time: 10:26 AM
 * To change this template use File | Settings | File Templates.
 */ 
class Netstarter_Extcatalog_Model_CatalogInventory_Observer extends Mage_CatalogInventory_Model_Observer
{

    /**
     * cache remove function
     *
     * @param Mage_Core_Model_Abstract $entity
     * @param array $ids
     */
    protected function _cleanEntityCache(Mage_Core_Model_Abstract $entity, array $ids)
    {
        $cacheTags = array();
        foreach ($ids as $entityId) {
            $entity->setId($entityId);
            $cacheTags = array_merge($cacheTags, $entity->getCacheIdTags());
        }
        if (!empty($cacheTags)) {
            Enterprise_PageCache_Model_Cache::getCacheInstance()->clean($cacheTags);
        }
    }


    /**
     * Remove FPC after order place
     *
     * @param $observer
     * @return $this
     */
    public function reindexQuoteInventory($observer)
    {
        // Reindex quote ids
        $quote = $observer->getEvent()->getQuote();
        $productIds = array();
        foreach ($quote->getAllItems() as $item) {
            $productIds[$item->getProductId()] = $item->getProductId();
            $children   = $item->getChildrenItems();
            if ($children) {
                foreach ($children as $childItem) {
                    $productIds[$childItem->getProductId()] = $childItem->getProductId();
                }
            }
        }

        if( count($productIds)) {
            Mage::getResourceSingleton('cataloginventory/indexer_stock')->reindexProducts($productIds);
        }

        // Reindex previously remembered items
        $productIds = array();
        foreach ($this->_itemsForReindex as $item) {
            $item->save();
            $productIds[] = $item->getProductId();
        }
        Mage::getResourceSingleton('catalog/product_indexer_price')->reindexProductIds($productIds);

//        if (is_array($productIds)) {
//            $this->_cleanEntityCache(Mage::getModel('catalog/product'), $productIds);
//        }

        $this->_itemsForReindex = array(); // Clear list of remembered items - we don't need it anymore

        return $this;
    }
}