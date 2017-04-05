<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gayan thrimanne
 * Date: 2/22/13
 * Time: 10:48 AM
 * To change this template use File | Settings | File Templates.
 */

class Netstarter_Seo_Model_Sitemap extends Mage_Sitemap_Model_Sitemap
{

    public function generateXml()
    {
        /*
         * if you want to have default xml sitemap, just disable Netstarter XML sitemap from config settings
         * */
        $isEnabled  = Mage::getStoreConfig('xmlsitemap/settings/xml_sitemapenable');
        if(!$isEnabled){
            return parent::generateXml();
        }

        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));

        if ($io->fileExists($this->getSitemapFilename()) && !$io->isWriteable($this->getSitemapFilename())) {
            Mage::throwException(Mage::helper('sitemap')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', $this->getSitemapFilename(), $this->getPath()));
        }

        $io->streamOpen($this->getSitemapFilename());

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');

        $storeId = $this->getStoreId();
        $date = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        /**
         * Generate categories sitemap
         */

        $changefreq = (string)Mage::getStoreConfig('sitemap/category/changefreq', $storeId);
        $priority = (string)Mage::getStoreConfig('sitemap/category/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);
        $categories = new Varien_Object();
        $categories->setItems($collection);
        Mage::dispatchEvent('sitemap_categories_generating_before', array(
            'collection' => $categories
        ));
        foreach ($categories->getItems() as $item) {

            $_current_category = Mage::getModel('catalog/category')->load($item->getId());
            $canshow = $_current_category->getResource()->getAttribute('cat_show_in_xml_sitemap')
                ->getFrontend()->getValue($_current_category);

            if ($canshow == "Hide")
                continue;

            $_current_category_freq = ($canshow != "Hide") ? $_current_category->getResource()->getAttribute('cat_frequency')
                ->getFrontend()->getValue($_current_category) : "";
            $_current_category_prio = ($canshow != "Hide") ? $_current_category->getResource()->getAttribute('cat_priority')
                ->getFrontend()->getValue($_current_category) : "";

            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                ($_current_category_freq != "" && $_current_category_freq != "No") ? $_current_category_freq : $changefreq,
                ($_current_category_prio != "" && $_current_category_prio != "No") ? $_current_category_prio : $priority
            );

            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate products sitemap
         */

        $changefreq = (string)Mage::getStoreConfig('sitemap/product/changefreq', $storeId);
        $priority = (string)Mage::getStoreConfig('sitemap/product/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);
        $products = new Varien_Object();
        $products->setItems($collection);
        Mage::dispatchEvent('sitemap_products_generating_before', array(
            'collection' => $products
        ));
        foreach ($products->getItems() as $item) {

            $_current_prod = Mage::getModel('catalog/product')->load($item->getId());

            $canshow = $_current_prod->getResource()->getAttribute('prod_show_in_xml_sitemap')
                ->getFrontend()->getValue($_current_prod);

            if ($canshow == "Hide")
                continue;

            $stock = $_current_prod->getStockItem();

            if (!$stock->getIsInStock()) {
                continue;
            }


            $_current_prod_freq = ($canshow != "Hide") ? $_current_prod->getResource()->getAttribute('prod_frequency')
                ->getFrontend()->getValue($_current_prod) : "";
            $_current_prod_prio = ($canshow != "Hide") ? $_current_prod->getResource()->getAttribute('prod_priority')
                ->getFrontend()->getValue($_current_prod) : "";


            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                ($_current_prod_freq != "" && $_current_prod_freq != "No") ? $_current_prod_freq : $changefreq,
                ($_current_prod_prio != "" && $_current_prod_prio != "No") ? $_current_prod_prio : $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate cms pages sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/page/changefreq', $storeId);
        $priority = (string)Mage::getStoreConfig('sitemap/page/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/cms_page')->getCollection($storeId);
        foreach ($collection as $item) {

            $_currentcmsPage = Mage::getModel('cms/page')->load($item->getId());

            $_current_page = Mage::getModel('netstarter_seo/seocms')->load($_currentcmsPage->getPageId(), 'page_id');

            $canshow = $_current_page->getShowInXmlsitemap();

            if ($canshow == 0)
                continue;

            $_current_page_freq = ($canshow != 0) ? $_current_page->getFrequency() : "";
            $_current_page_prio = ($canshow != 0) ? $_current_page->getPriority() : "";

            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                ($_current_page_freq != "") ? $_current_page_freq : $changefreq,
                ($_current_page_prio != "") ? $_current_page_prio : $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        $io->streamWrite('</urlset>');
        $io->streamClose();

        $this->setSitemapTime(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return $this;
    }

}
