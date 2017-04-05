<?php
/**
 * @author Prasad
 *
 * Class Netstarter_Location_Model_Processor_Location
 */
class Netstarter_Quickview_Model_Processor_View extends Enterprise_PageCache_Model_Processor_Default
{

    /**
     * Key for saving product id in metadata
     */
    const METADATA_QV_PRODUCT_ID = 'qv_product_id';

    /**
     * Prepare response body before caching
     *
     * @param Zend_Controller_Response_Http $response
     * @return string
     */
    public function prepareContent(Zend_Controller_Response_Http $response)
    {
        $cacheInstance = Enterprise_PageCache_Model_Cache::getCacheInstance();

        /** @var Enterprise_PageCache_Model_Processor */
        $processor = Mage::getSingleton('enterprise_pagecache/processor');

        // save current product id
        $product = Mage::registry('current_product');
        if ($product) {
            $cacheId = $processor->getRequestCacheId() . '_qv_product_id';
            $cacheInstance->save($product->getId(), $cacheId, array(Enterprise_PageCache_Model_Processor::CACHE_TAG));
            $processor->setMetadata(self::METADATA_QV_PRODUCT_ID, $product->getId());
        }

        return parent::prepareContent($response);
    }
}
