<?php

class Smartwave_Porto_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    protected $_texturePath;
    
    public function __construct()
    {
        $this->_texturePath = 'wysiwyg/porto/texture/default/';
    }

    public function getCfgGroup($group, $storeId = NULL)
    {
        if ($storeId)
            return Mage::getStoreConfig('porto/' . $group, $storeId);
        else
            return Mage::getStoreConfig('porto/' . $group);
    }
    
    public function getCfgSectionDesign($storeId = NULL)
    {
        if ($storeId)
            return Mage::getStoreConfig('porto_design', $storeId);
        else
            return Mage::getStoreConfig('porto_design');
    }

    public function getCfgSectionSettings($storeId = NULL)
    {
        if ($storeId)
            return Mage::getStoreConfig('porto_settings', $storeId);
        else
            return Mage::getStoreConfig('porto_settings');
    }
    
    public function getTexturePath()
    {
        return $this->_texturePath;
    }

    public function getCfg($optionString)
    {
        return Mage::getStoreConfig('porto_settings/' . $optionString);
    }
     public function getImage($product, $imgWidth, $imgHeight, $imgVersion='small_image', $file=NULL) 
    {
        $url = '';
        if ($imgHeight <= 0)
        {
            $url = Mage::helper('catalog/image')
                ->init($product, $imgVersion, $file)
                //->constrainOnly(true)
                ->keepAspectRatio(true)
                //->setQuality(100)
                ->keepFrame(false)
                ->resize($imgWidth);
        }
        else
        {
            $url = Mage::helper('catalog/image')
                ->init($product, $imgVersion, $file)
                ->resize($imgWidth, $imgHeight);
        }
        return $url;
    }
    
    // get hover image for product
    public function getHoverImageHtml($product, $imgWidth, $imgHeight, $imgVersion='small_image') 
    {
        $product->load('media_gallery');
        $order = $this->getConfig('category/image_order');
        if ($gallery = $product->getMediaGalleryImages())
        {
            if ($hoverImage = $gallery->getItemByColumnValue('position', $order))
            {
                $url = '';
                if ($imgHeight <= 0)
                {
                    $url = Mage::helper('catalog/image')
                        ->init($product, $imgVersion, $hoverImage->getFile())
                        ->constrainOnly(true)
                        ->keepAspectRatio(true)
                        ->keepFrame(false)
                        ->resize($imgWidth);
                }
                else
                {
                    $url = Mage::helper('catalog/image')
                        ->init($product, $imgVersion, $hoverImage->getFile())
                        ->resize($imgWidth, $imgHeight);
                }
                return '<img class="hover-image" src="' . $url . '" alt="' . $product->getName() . '" />';
            }
        }
        
        return '';
    }
    public function getHomeUrl() {
        return array(
            "label" => $this->__('Home'),
            "title" => $this->__('Home Page'),
            "link" => Mage::getUrl('')
        );
    }
    public function getPreviousProduct()
    {
        $_prev_prod = NULL;
        $_product_id = Mage::registry('current_product')->getId();

        $cat = Mage::registry('current_category');
        if($cat) {
            $category_products = $cat->getProductCollection()->addAttributeToSort('position', 'asc');
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($category_products);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($category_products);

            $store = Mage::app()->getStore();
            $code = $store->getCode();
            if (!Mage::getStoreConfig("cataloginventory/options/show_out_of_stock", $code))
                Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($category_products);

            $items = $category_products->getItems();
            $cat_prod_ids = (array_keys($items));

            $_pos = array_search($_product_id, $cat_prod_ids); // get position of current product

            // get the next product url
            if (isset($cat_prod_ids[$_pos - 1])) {
                $_prev_prod = Mage::getModel('catalog/product')->load($cat_prod_ids[$_pos - 1]);
            } else {
                return false;
            }
        }
        if($_prev_prod != NULL){
            return $_prev_prod->getUrlPath();
        } else {
            return false;
        }
 
    }
 
 
    public function getNextProduct()
    {
        $_next_prod = NULL;
        $_product_id = Mage::registry('current_product')->getId();

        $cat = Mage::registry('current_category');

        if($cat) {
            $category_products = $cat->getProductCollection()->addAttributeToSort('position', 'asc');
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($category_products);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($category_products);

            $store = Mage::app()->getStore();
            $code = $store->getCode();
            if (!Mage::getStoreConfig("cataloginventory/options/show_out_of_stock", $code))
                Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($category_products);

            $items = $category_products->getItems();
            $cat_prod_ids = (array_keys($items));

            $_pos = array_search($_product_id, $cat_prod_ids); // get position of current product

            // get the next product url
            if (isset($cat_prod_ids[$_pos + 1])) {
                $_next_prod = Mage::getModel('catalog/product')->load($cat_prod_ids[$_pos + 1]);
            } else {
                return false;
            }
        }

        if($_next_prod != NULL){
            return $_next_prod->getUrlPath();
        } else {
            return false;
        }
    }
    public function getCompareUrl() {
        $_helper = Mage::helper("catalog/product_compare");
        return $_helper->getListUrl();
    }
}
