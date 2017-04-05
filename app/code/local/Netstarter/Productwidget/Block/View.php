<?php
/**
 * Created by Netstarter Pty Ltd.
 * User: Dilhan Maduranga
 * Date: 5/21/13
 * Time: 11:36 AM
 */
class Netstarter_Productwidget_Block_View extends Mage_Catalog_Block_Product_Abstract implements Mage_Widget_Block_Interface
{

    const PRODUCT_WIDGET_DEF_MODE = 1;
    const PRODUCT_WIDGET_DEF_TITLE = 'Best Sellers';

    protected $_productAttributesToLoad = array(
        'name', 'small_image', 'features','more_colors','is_noncore','status'
    );

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->setTemplate('productwidget/template_default.phtml');

        if($cacheObjPro = Mage::registry('product')){

            $catTags =  $cacheObjPro->getCacheTags();
        }elseif($cacheObjCat = Mage::registry('current_category')){

            $catTags =  $cacheObjCat->getCacheTags();
        }else{
            $catTags =  array(Mage_Catalog_Model_Product::CACHE_TAG);
        }

        $this->addData(array(
            'cache_lifetime'    => 86400,
            'cache_tags'        => $catTags
        ));

        parent::_construct();
    }

    public function getDisplayMode()
    {
        if (!$this->_getData('display_mode')) {
            $this->setData('display_mode', self::PRODUCT_WIDGET_DEF_MODE);
        }
        return $this->_getData('display_mode');
    }

    public function getDisplayTitle()
    {
        if (!$this->_getData('display_title')) {
            $this->setData('display_title', self::PRODUCT_WIDGET_DEF_TITLE );
        }
        return str_replace(' ','|',$this->_getData('display_title'));
    }

    public function getProductIds()
    {
        if (!$this->_getData('product_ids')) {
            $this->setData('product_ids','');
        }
        return $this->_getData('product_ids');
    }

    protected function setProductAttributesToLoad($attr = array())
    {
        $this->_productAttributesToLoad = $attr;
    }

    public function getCacheKeyInfo()
    {
        $items = array(
            'name' => $this->getNameInLayout(),
            'display_mode' => $this->getDisplayMode(),
            'product_ids' => $this->getProductIds(),
            'display_title' => (string) $this->getDisplayTitle()
        );

        if ($this->getDisplayMode() == 3) {
            $items['product_id'] = Mage::registry('product')->getId();
        }

        $items = parent::getCacheKeyInfo() + $items;

        return $items;
    }

    /**
     *
     * Add additional attributes
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _addProductAttributesAndPrices(Mage_Catalog_Model_Resource_Product_Collection $collection)
    {
        return $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect($this->_productAttributesToLoad)
            ->addUrlRewrite();
    }

    /**
     * Using the widget parameter product_ids, return an array of product Ids
     * @return array || array of product ids
     */
    public function getProductList()
    {
        $productIds = array();

        $productIdStr = trim($this->getProductIds());

        $displayMode = $this->getDisplayMode();

        $storeId = Mage::app()->getStore()->getId();
        $website_id = Mage::app()->getWebsite()->getId();

        $products = Mage::getModel('catalog/product')->getCollection()
            ->addStoreFilter($storeId)
            ->addFieldToFilter('status',Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        $this->_addProductAttributesAndPrices($products);

        $products->getSelect()->join(array('inv' => 'cataloginventory_stock_status_idx'),
            'inv.product_id = e.entity_id AND inv.website_id = '.$website_id, 'stock_status');

        $products->getSelect()->limit(25);

        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        switch($displayMode){

            case 1:

                if(empty($productIdStr)){
                    return null;
                }else{
                    $productIds = explode(',', $productIdStr);
                }

                if ($productIds) {

                    $products->getSelect()->where('e.entity_id IN (?)',$productIds)
                        ->order(new Zend_Db_Expr("FIELD(e.entity_id, $productIdStr)"));

                    return $products;
                }

                break;

            case 2:

                $product = Mage::registry('product');

                if($product){

                    $productIds = $product->getUpSellProductIds();
                    if(empty($productIds)) return null;

                    $this->setData('product_ids',implode(',', $productIds));
                    $this->setData('display_mode', self::PRODUCT_WIDGET_DEF_MODE);

                    $products->getSelect()->where('e.entity_id IN (?)',$productIds);

                    return $products;
                }

                break;

            case 3:

                $product = Mage::registry('product');

                if($product){

                    $looks = Mage::getResourceModel('productwidget/look_collection')
                        ->addFieldToSelect('linked_product_id')
                        ->addFieldToFilter('product_id', $product->getId());

                    $looks->getSelect()->order('position DESC');

                    if($looks){

                        foreach($looks as $look){
                            $productIds[] = $look->getLinkedProductId();
                        }

                        if(empty($productIds)) return null;

                        $productIdStr = implode(',', $productIds);
                        $this->setData('product_ids',$productIdStr);
                        $this->setData('display_mode', self::PRODUCT_WIDGET_DEF_MODE);

                        $products->getSelect()->where('e.entity_id IN (?)',$productIds)
                            ->order(new Zend_Db_Expr("FIELD(e.entity_id, $productIdStr)"));
                        return $products;
                    }
                }

                break;
        }

        return null;
    }


    public function getReviewBox($product)
    {
        $reviewHelper = $this->getLayout()->createBlock('review/helper');
        $reviewHelper->addTemplate('productwidget', 'productwidget/review/helper/summary.phtml');
        return $reviewHelper->getSummaryHtml($product, 'productwidget', false);
    }
}
