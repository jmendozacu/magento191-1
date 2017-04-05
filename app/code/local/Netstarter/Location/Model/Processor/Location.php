<?php
/**
 * @author Prasad
 *
 * Class Netstarter_Location_Model_Processor_Location
 */
class Netstarter_Location_Model_Processor_Location extends Enterprise_PageCache_Model_Processor_Default
{

    const METADATA_STORE_ID = 'current_store_id';

    private $_placeholder;

    /**
     * Cache the breadcrumb also
     *
     * @param string $content
     * @return mixed|string
     * @throws Exception
     */
    public function replaceContentToPlaceholderReplacer($content)
    {
        $placeholders = array();
        preg_match_all(
            Enterprise_PageCache_Model_Container_Placeholder::HTML_NAME_PATTERN,
            $content,
            $placeholders,
            PREG_PATTERN_ORDER
        );
        $placeholders = array_unique($placeholders[1]);
        try {
            foreach ($placeholders as $definition) {

                $this->_placeholder = Mage::getModel('enterprise_pagecache/container_placeholder', $definition);
                if($this->_placeholder->getName() != 'PAGE_BREADCRUMBS'){
                    $content = preg_replace_callback($this->_placeholder->getPattern(),
                        array($this, '_getPlaceholderReplacer'), $content);
                }
            }
            $this->_placeholder = null;
        } catch (Exception $e) {
            $this->_placeholder = null;
            throw $e;
        }
        return $content;
    }


    protected function _getPlaceholderReplacer($matches)
    {
        $container = $this->_placeholder->getContainerClass();
        /**
         * In developer mode blocks will be rendered separately
         * This should simplify debugging _renderBlock()
         */
        if ($container && !Mage::getIsDeveloperMode()) {
            $container = new $container($this->_placeholder);
            $container->setProcessor(Mage::getSingleton('enterprise_pagecache/processor'));
            $blockContent = $matches[1];
            $container->saveCache($blockContent);
        }
        return $this->_placeholder->getReplacer();
    }

    /**
     * full page cache processor
     *
     * @param Zend_Controller_Response_Http $response
     * @return string
     */
    public function prepareContent(Zend_Controller_Response_Http $response)
    {

        $cacheInstance = Enterprise_PageCache_Model_Cache::getCacheInstance();

        /** @var Enterprise_PageCache_Model_Processor */
        $processor = Mage::getSingleton('enterprise_pagecache/processor');

        $store = Mage::registry('current_store');

        if ($store) {

            $cacheId = $processor->getRequestCacheId() . '_current_location_id';
            $cacheInstance->save($store->getId(), $cacheId, array(Enterprise_PageCache_Model_Processor::CACHE_TAG));
            $processor->setMetadata(self::METADATA_STORE_ID, $store->getId());
        }else{

            $processor->setMetadata(self::METADATA_STORE_ID, 'DEF'.Mage::app()->getStore()->getId());
        }

        return parent::prepareContent($response);
    }
}
