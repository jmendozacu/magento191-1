<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gayan T
 * Date: 6/10/13
 * Time: 2:56 PM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_Seo_Block_Canonical extends Mage_Core_Block_Template
{

    /*get custom canonical url for products*/
    public function getCanonicalUrl()
    {
        $url = null;
        $isOn = Mage::getStoreConfig('catalog/seo/product_canonical_tag_netstarter');

        $product = Mage::registry('current_product'); // get current product

        if($isOn==1 && $product->getCanonicalTag())
        {
            $baseUrl = rtrim(Mage::getBaseUrl(), '/').'/';
            $url = $baseUrl.''.str_replace($baseUrl,'',$product->getCanonicalTag());;
        } else {
            $url = $this->helper('core/url')->getCurrentUrl(); // get current product url
        }

        return $url;
    }

    /*get custom canonical url for categories*/
    public function getCanonicalCategoryUrl()
    {
        $url = null;
        $isOn = Mage::getStoreConfig('catalog/seo/category_canonical_tag_netstarter');

        $category = Mage::registry('current_category'); // get current category
        $baseUrl = rtrim(Mage::getBaseUrl(), '/').'/';

        if($isOn == 1 && $category->getCanonicalTag())
        {
            $url = $baseUrl.''.str_replace($baseUrl,'',$category->getCanonicalTag());
        } else {
            $url = $baseUrl.''.str_replace($baseUrl,'',$category->getUrlPath()); // get current category url

        }

        return $url;
    }

    public function getCanonicalCmsUrl(){
        $cmsPageUrl = Mage::registry('canonical_cms_url');
        if($cmsPageUrl)
        {
            $baseUrl = rtrim(Mage::getBaseUrl(), '/').'/';
            $url = $baseUrl.''.str_replace($baseUrl,'', $cmsPageUrl);
        } else {
            $url = $this->helper('core/url')->getCurrentUrl(); // get current category url
        }

        return $url;
    }
}