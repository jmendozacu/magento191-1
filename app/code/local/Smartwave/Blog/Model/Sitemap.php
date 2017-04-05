<?php

class Smartwave_Blog_Model_Sitemap extends Harapartners_SitemapXml_Model_Rewrite_Sitemap_Sitemap//Mage_Sitemap_Model_Sitemap
{
    protected $_io;

    public function generateXml()
    {
        $exclude = array();
        $urls = Mage::getStoreConfig('inchoo/exclude/urls');
        if ($urls) {
            $urls = unserialize($urls);
            if (is_array($urls))
                foreach ($urls as $url)
                    $exclude[] = $url['url'];
        }

        if (Mage::helper('blog')->extensionEnabled('Smartwave_Ascurl')) {
            return Mage::getModel('ascurl/sitemap')->setData($this->getData())->generateXml();
        }

        $storeId = $this->getStoreId();
        $date = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        if (end(explode('/', trim($this->getPath(), '/'))) == 'video') {

            $this->videoFileCreate();

            $collection = Mage::getModel('iwd_productvideo/video')
                ->getCollection();
            foreach ($collection as $item){
                if(unserialize($item->getData('video_store_view'))[0] == '4') {
                    $prodId = Mage::getModel('iwd_productvideo/productvideo')->load($item->getData('video_id'), 'video_id')->getData('product_id');
                        $prod = Mage::getModel('catalog/product')
                            ->setStoreId($storeId)->load($prodId);
                        $loc = $prod->getData('url_path');
                    if($loc){
                        $loc = $baseUrl . $loc;
                        $thumbnailLoc = $baseUrl . "media/iwd_video/img/" . $item->getData('image');
                        $title = $item->getData('title');
                        $playerLoc = "https://fast.wistia.net/embed/iframe/" . $item->getData('url');
                        $xml = sprintf('<url><loc>%s</loc>
<video:video>
<video:thumbnail_loc>%s</video:thumbnail_loc>
<video:title>%s</video:title>
<video:description>%s</video:description>
<video:player_loc>%s</video:player_loc>
</video:video></url>', $loc, $thumbnailLoc, $title, $title, $playerLoc);

                        $this->sitemapFileAddLine($xml);
                    }
                }
            }
unset($collection);
        } else {

        $this->fileCreate();
        /**
         * Generate categories sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/category/changefreq');
        $priority = (string)Mage::getStoreConfig('sitemap/category/priority');
        $collection = Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);
        foreach ($collection as $item) {
            foreach ($exclude as $url)
                if (strpos($item->getUrl(),$url) !== false)
                    continue 2;

            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()), $date, $changefreq, $priority
            );
            //$newXml = $this->_insertLanguageXml($baseUrl, $item, 'product', $xml);
            $this->sitemapFileAddLine($xml);
        }
        unset($collection);

        /**
         * Generate products sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/product/changefreq');
        $priority = (string)Mage::getStoreConfig('sitemap/product/priority');
        $collection = Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);
        foreach ($collection as $item) {
            foreach ($exclude as $url)
                if (strpos($item->getUrl(),$url) !== false)
                    continue 2;
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()), $date, $changefreq, $priority
            );
            //$newXml = $this->_insertLanguageXml($baseUrl, $item, 'product', $xml);
            $this->sitemapFileAddLine($xml);
        }
        unset($collection);

        /**
         * Generate cms pages sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/page/changefreq');
        $priority = (string)Mage::getStoreConfig('sitemap/page/priority');
        $collection = Mage::getResourceModel('sitemap/cms_page')->getCollection($storeId);
        foreach ($collection as $item) {
            foreach ($exclude as $url)
                if (strpos($item->getUrl(),$url) !== false)
                    continue 2;
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()), $date, $changefreq, $priority
            );
            $this->sitemapFileAddLine($xml);
        }
        unset($collection);

            //Mage::dispatchEvent('sitemap_add_xml_block_to_the_end', array('sitemap_object' => $this));

        }
        $this->fileClose();

        $this->setSitemapTime(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return $this;
    }

    protected function fileCreate()
    {
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));
        $io->streamOpen($this->getSitemapFilename());

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
        $this->_io = $io;
    }

    protected function videoFileCreate(){
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));
        $io->streamOpen($this->getSitemapFilename());

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $io->streamWrite('<urlset xmlns:xhtml="http://www.sitemaps.org/schemas/sitemap/0.9"
                                  xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">');
        $this->_io = $io;
    }

    protected function fileClose()
    {
        $this->_io->streamWrite('</urlset>');
        $this->_io->streamClose();
    }

    public function sitemapFileAddLine($xml)
    {
        $this->_io->streamWrite($xml);
    }
}