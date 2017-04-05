<?php
/**
 * Catalog category url
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Netstarter_Extcatalog_Model_Category_Url extends Enterprise_Catalog_Model_Category_Url
{
    /**
     * Retrieve Url for specified category
     *
     * @param Mage_Catalog_Model_Category $category
     * @return string
     */
    public function getCategoryUrl(Mage_Catalog_Model_Category $category)
    {
        $a = $category->getData();
        // custom_link_url is loaded from the collection. but in case its not loaded from somewhere, manually load the category item
        if (!$category->hasData('custom_link_url')) {
            $category->load($category->getId());
        }

        $customLinkUrl = $category->getData('custom_link_url');

        $url = (! empty($customLinkUrl)) ? trim(Mage::getUrl($customLinkUrl), '/') : $category->getData('url');

        if (null !== $url) {
            return $url;
        }

        Varien_Profiler::start('REWRITE: '.__METHOD__);

        if ($category->hasData('request_path') && $category->getData('request_path') != '') {
            $category->setData('url', $this->_getDirectUrl($category));
            Varien_Profiler::stop('REWRITE: '.__METHOD__);
            return $category->getData('url');
        }

        $requestPath = $this->_getRequestPath($category);
        if ($requestPath) {
            $category->setRequestPath($requestPath);
            $category->setData('url', $this->_getDirectUrl($category));
            Varien_Profiler::stop('REWRITE: '.__METHOD__);
            return $category->getData('url');
        }

        Varien_Profiler::stop('REWRITE: '.__METHOD__);

        $category->setData('url', $category->getCategoryIdUrl());
        return $category->getData('url');
    }
}
