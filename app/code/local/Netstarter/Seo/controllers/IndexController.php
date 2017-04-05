<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gayan
 * Date: 7/24/13
 * Time: 9:31 AM
 * To change this template use File | Settings | File Templates.
 */

class Netstarter_Seo_IndexController extends Mage_Adminhtml_Controller_Action{

    private $StoreId;
    private $StoreUrl = '';
    private $ProdUrlSuffix = '';
    private $ProdCatUrlSuffix = '';


    /*
     * get some information on store id, store url, product and category url suffix
     * */
    private function setInfo(){
        $storeCode = Mage::app()->getRequest()->getParam('store');
        $store = Mage::getModel("core/store")->load($storeCode);
        $this->StoreId  = $store->getId();
        $store_url = explode('/?',$store->getUrl());
        $this->StoreUrl = rtrim($store_url[0], '/').'/';
        $this->ProdUrlSuffix = Mage::getStoreConfig('catalog/seo/product_url_suffix',$this->StoreId);
        $this->ProdCatUrlSuffix = Mage::getStoreConfig('catalog/seo/category_url_suffix',$this->StoreId);
    }


    /* read the contents of csv file uploaded*/
    public function getCsvFile(){
        $filename = Mage::getStoreConfig('nswebredirects/settings/upload',$this->StoreId);
        if ($filename and file_exists(Mage::getBaseDir('media') . '/netstarter/redirects/' . $filename))
        {
            Mage::log('Start - Updating Mass 301 Redirects - '.$filename.' - Store : '.$this->StoreId, null, 'mass301redirects.log');
            return file(Mage::getBaseDir('media') . '/netstarter/redirects/' . Mage::getStoreConfig('nswebredirects/settings/upload',$this->StoreId));
        }
        return null;
    }

    /* get attribute value for given option label*/
    private function getAttributeValue($attribute, $label){
        $productModel = Mage::getModel('catalog/product');
        $attr = $productModel->getResource()->getAttribute($attribute);
        if ($attr) {
            return $attr->getSource()->getOptionId($label);
        }
        return null;
    }

    /* get attribute value for given option label*/
    private function getCatAttributeValue($attribute, $label){
        $productModel = Mage::getModel('catalog/category');
        $attr = $productModel->getResource()->getAttribute($attribute);
        if ($attr) {
            return $attr->getSource()->getOptionId($label);
        }
        return null;
    }

    /* update product seo info with given xml data*/
    private function updateProductSeo($data, $productId){
        try{
            $oProduct = Mage::getModel('catalog/product')->load($productId);
            if(isset($data['h1']) && $data['h1']){
                $oProduct->setProductH1Name($data['h1']);
            }
            if(isset($data['metatitle']) && $data['metatitle']){
                $oProduct->setMetaTitle($data['metatitle']);
            }
            if(isset($data['htmlsitemap']) && strtolower($data['htmlsitemap'])=='yes'){
                $oProduct->setProdShowInHtmlSitemap($this->getAttributeValue('prod_show_in_html_sitemap', 'show'));
            }
            if(isset($data['htmlsitemap']) && strtolower($data['htmlsitemap'])=='no'){
                $oProduct->setProdShowInHtmlSitemap($this->getAttributeValue('prod_show_in_html_sitemap', 'hide'));
            }
            if(isset($data['xmlsitemap']) && strtolower($data['xmlsitemap'])=='yes'){
                $oProduct->setProdShowInXmlSitemap($this->getAttributeValue('prod_show_in_xml_sitemap', 'show'));
            }
            if(isset($data['xmlsitemap']) && strtolower($data['xmlsitemap'])=='no'){
                $oProduct->setProdShowInXmlSitemap($this->getAttributeValue('prod_show_in_xml_sitemap', 'hide'));
            }
            if(isset($data['frequency']) && $data['frequency']){
                $oProduct->setProdFrequency($this->getAttributeValue('prod_frequency', $data['frequency']));
            }
            if(isset($data['priority']) && $data['priority']){
                $oProduct->setProdPriority($this->getAttributeValue('prod_priority', $data['priority']));
            }
            if(isset($data['canonicalurl']) && $data['canonicalurl']){
                $oProduct->setCanonicalTag($data['canonicalurl']);
            }
            if(isset($data['robottags']) && $data['robottags']){
                $oProduct->setRobotTags($this->getAttributeValue('robot_tags', $data['robottags']));
            }
            $oProduct->save();
        }
        catch (Exception $e){
            Mage::app()->getResponse()->setBody("Error Occurred When Updating Product Attributes");
            Mage::log($e, null, 'mass_seo_updates.log');
        }
    }

    /* update product category seo info with given xml data*/
    private function  updateCategorySeo($data, $catId){
        try{
            $oCat = Mage::getModel('catalog/category')->load($catId);
            if(isset($data['h1']) && $data['h1']){
                $oCat->setCategoryH1Name($data['h1']);
            }
            if(isset($data['metatitle']) && $data['metatitle']){
                $oCat->setMetaTitle($data['metatitle']);
            }
            if(isset($data['htmlsitemap']) && strtolower($data['htmlsitemap'])=='yes'){
                $oCat->setCatShowInHtmlSitemap($this->getCatAttributeValue('cat_show_in_html_sitemap', 'show'));
            }
            if(isset($data['htmlsitemap']) && strtolower($data['htmlsitemap'])=='no'){
                $oCat->setCatShowInHtmlSitemap($this->getCatAttributeValue('cat_show_in_html_sitemap', 'hide'));
            }
            if(isset($data['xmlsitemap']) && strtolower($data['xmlsitemap'])=='yes'){
                $oCat->setCatShowInXmlSitemap($this->getCatAttributeValue('cat_show_in_xml_sitemap', 'show'));
            }
            if(isset($data['xmlsitemap']) && strtolower($data['xmlsitemap'])=='no'){
                $oCat->setCatShowInXmlSitemap($this->getCatAttributeValue('cat_show_in_xml_sitemap', 'hide'));
            }
            if(isset($data['frequency']) && $data['frequency']){
                $oCat->setCatFrequency($this->getCatAttributeValue('cat_frequency', $data['frequency']));
            }
            if(isset($data['priority']) && $data['priority']){
                $oCat->setCatPriority($this->getCatAttributeValue('cat_priority', $data['priority']));
            }
            if(isset($data['canonicalurl']) && $data['canonicalurl']){
                $oCat->setCanonicalTag($data['canonicalurl']);
            }
            if(isset($data['robottags']) && $data['robottags']){
                $oCat->setRobotTags($this->getCatAttributeValue('robot_tags', $data['robottags']));
            }
            $oCat->save();
        }
        catch (Exception $e){
            Mage::app()->getResponse()->setBody("Error Occurred When Updating Product Category Attributes");
            Mage::log($e, null, 'mass_seo_updates.log');
        }
    }

    /* Mass SEO Attribute Updater - read each child node*/
    private function updateSeo($child){
        $data = get_object_vars($child);
        if($data['url']){
            $oRewrite = Mage::getModel('core/url_rewrite')
                ->setStoreId($this->StoreId)
                ->loadByRequestPath(str_replace($this->StoreUrl, '', $data['url']));
            $iProductId = $oRewrite->getProductId();
            $iProductCatId = $oRewrite->getCategoryId();
            if($iProductId){
                $this->updateProductSeo($data, $iProductId);
                return true;
            }
            else if($iProductCatId && !$iProductId){
                $this->updateCategorySeo($data, $iProductCatId);
                return true;
            }
        }
        return false;
    }

    /* Mass SEO Attribute Updater*/
    public function seoAction(){
        $this->setInfo();
        $filename = Mage::getStoreConfig('nswebredirects/seomassupdate/upload',$this->StoreId);
        $xmlPath = Mage::getBaseDir('media') . '/netstarter/seo/' . $filename;
        if ($filename and file_exists($xmlPath))
        {
            try{
                Mage::log('Start - Updating Mass SEO Attributes, Store ID : ' . $this->StoreId, null, 'mass_seo_updates.log');
                $xml = simplexml_load_file($xmlPath);
                $count = 0;
                $tot = 0;
                foreach($xml->children() as $child)
                {
                    $tot++;
                    if($this->updateSeo($child)){
                        $count++;
                    }
                }
                Mage::log('End - Updating Mass SEO Attributes, Success : '.$count.', Total : '.$tot, null, 'mass_seo_updates.log');
                Mage::app()->getResponse()->setBody("Mass SEO Attributes
                        \n\n - Updated ".$count.",
                        \n - Total : ".$tot);
            }
            catch (Exception $e){
                Mage::app()->getResponse()->setBody("Error Occurred When reading XML file");
                Mage::log($e, null, 'mass_seo_updates.log');
            }
        }
    }

    /*Mass 301 redirect - url rewrite*/
    public function indexAction()
    {

        $this->setInfo();
        $csv_file = $this->getCsvFile();
        if($csv_file){
            try{
                $count=0;
                $failed = 0;
                $exists = 0;
                $tot = 0;
                foreach ($csv_file as $redirectLine)
                {
                    $tot++;
                    $sourceDestination = explode(',', $redirectLine);
                    $sourceUrl = rtrim(trim($sourceDestination[0]), '/');
                    $newUrl = "";
                    if(isset($sourceDestination[1])){
                        $newUrl = rtrim(trim($sourceDestination[1]), '/');
                        $newUrlA = explode('/', $newUrl);
                        $newUrl = end($newUrlA);
                    }
                    if ($newUrl == "" or $sourceUrl == ""){
                        continue;
                    }
                    $oRewrite = Mage::getModel('core/url_rewrite')
                        ->setStoreId($this->StoreId)
                        ->loadByRequestPath(str_replace($this->StoreUrl, '', $sourceUrl));
                    $iProductId = $oRewrite->getProductId();
                    $iProductCatId = $oRewrite->getCategoryId();

                    /* if this rewrite url is associated with a product id,
                    update new url key with rewrite history for 301 redirects*/
                    if($iProductId){
                        //remove last occurrence of ProdUrlSuffix
                        $newUrl = substr_replace($newUrl, '', strrpos($newUrl, $this->ProdUrlSuffix), strlen($this->ProdUrlSuffix));
                        $oProduct = Mage::getModel('catalog/product')->load($iProductId);
                        if($oProduct->getUrlKey() != $newUrl){
                            $oProduct->setUrlKey($newUrl);
                            $oProduct->setData('save_rewrites_history', true);
                            $oProduct->save();
                            $count++;
                        }
                        else{
                            $exists++;
                        }
                    }

                    /*
                     * if it is not associated with a product id, then check where it associate with category id,
                     * then update the new key with rewrite history for 301 redirects
                     * */
                    else if($iProductCatId && !$iProductId){

                        $newUrl = substr_replace($newUrl, '', strrpos($newUrl, $this->ProdCatUrlSuffix), strlen($this->ProdCatUrlSuffix));
                        $oCategory = Mage::getModel('catalog/category')->load($iProductCatId);
                        if ($oCategory->getUrlKey() != $newUrl){
                            $oCategory->setUrlKey($newUrl);
                            $oCategory->setData('save_rewrites_history', true);
                            $oCategory->save();
                            $count++;
                        }
                        else{
                            $exists++;
                        }
                    }
                    else{
                        $failed++;
                    }
                }

                Mage::log('End - Updating Mass 301 Redirects - Updated '.$count.' Urls - Failed '.$failed.' Urls - Already Exists '.$exists.' Urls - Total : '.$tot.' Urls', null, 'mass301redirects.log');
                Mage::app()->getResponse()->setBody("Mass 301 Redirect
                        \n\n - Updated ".$count." Urls
                        \n - Failed : ".$failed." Urls
                        \n - Already Exists : ".$exists." Urls
                        \n - Total : ".$tot." Urls");

            }
            catch (Exception $e){
                Mage::app()->getResponse()->setBody("Error Occured");
                Mage::log($e, null, 'mass301redirects.log');
            }
        }
        else{
            Mage::app()->getResponse()->setBody("File Error");
            Mage::log('Mass 301 File error', null, 'mass301redirects.log');
        }

    }
}