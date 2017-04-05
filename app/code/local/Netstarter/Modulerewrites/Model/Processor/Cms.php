<?php
/**
 * @author Prasad
 *
 * Class Netstarter_Location_Model_Processor_Location
 */
class Netstarter_Modulerewrites_Model_Processor_Cms extends Enterprise_PageCache_Model_Processor_Default
{

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
}
