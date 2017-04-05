<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dilhan
 * Date: 6/19/13
 * @namespace   : Netstarter
 * @Module      : Netstarter_Quickview
 */

class Netstarter_Switcher_Block_Switcher extends Mage_Core_Block_Template
{
    protected $_product = null;
    protected $_siblingProductType = 'configurable';
    protected $_switcher = '';
    protected $_isAjax = true;
    protected $_filterProIds = null;

    const MAX_PRODUCT_LIMIT  = 50;

    const DEFAULT_IMG_DIMENSIONS = '25px';

    /**
     * @return string
     */
    public function getSiblingProductType()
    {
        return $this->_siblingProductType;
    }


    /**
     * @method  getProduct
     *          If $this->_product is not set, call setProduct and try to get from Mage::registry
     *          If availble, return the product
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->setProduct();
        }
        if ($this->_product instanceof Mage_Catalog_Model_Product){
            return $this->_product;
        }
    }

    /**
     * @method  setProduct
     *          If product is given, set it to class property else get it from Mage::registry
     * @param null $product
     */
    public function setProduct($product=null)
    {
        if ($product) {
            $this->_product = $product;
        } else {
            $this->_product = Mage::registry('product');
        }
        return $this;
    }

    public function setFilters($proIds)
    {
        $this->_filterProIds = $proIds;
    }

    public function noAjax()
    {
        $this->_isAjax = false;
    }

    public function isAjax()
    {
        return $this->_isAjax;
    }

    public function getImageDimensions()
    {
        return self::DEFAULT_IMG_DIMENSIONS;
    }

    public function getSiblingProducts()
    {
        $storeId = Mage::app()->getStore()->getId();
        $website_id = Mage::app()->getWebsite()->getId();

        $products = Mage::getResourceModel('catalog/product_collection')
            ->addStoreFilter($storeId)
            ->addAttributeToFilter(array(array('attribute' => 'type_id','eq' => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE),
                                    array('attribute' => 'type_id','eq' => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)))
            ->addFieldToFilter('status',Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->addFieldToFilter('item_code', $this->getProduct()->getItemCode());

        $products->addAttributeToSelect(array('name','color', 'simple_color','is_noncore'))
                ->addFinalPrice()
                ->addTaxPercents();

        $products->getSelect()->join(array('inv' => 'cataloginventory_stock_status_idx'),
            'inv.product_id = e.entity_id AND inv.website_id = '.$website_id, 'stock_status');

        $products->getSelect()->limit(self::MAX_PRODUCT_LIMIT);

        if(!is_null($this->_filterProIds)){

            $products->getSelect()->where('e.entity_id IN (?)',$this->_filterProIds);
        }

        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        return $products;
    }

    public function getSwitchableProducts()
    {
        $products = $this->getSiblingProducts();
        $productsToShow = array();

        if ($products) {
            foreach($products as $product) {

                if ($product->isSaleable()){
                    $productsToShow[$product->getId()] = $product;
                }
            }
        }
        return $productsToShow;
    }

    public function getQuickViewUrl($product)
    {
        if ($this->_isAjax) {
            return Mage::helper('quickview')->getQuickViewUrl($product->getId(), $this->getType());
        }else{
            return $product->getProductUrl();
        }
    }

    /**
     * Get attribute Option Code by its code
     *
     * @param $product Relevant Product
     * @param $attributeCode Code of the attribute
     * @return string
     */
    public function getAttributeValue($product, $attributeCode)
    {
        $attributeLabel = '';
        if ($product) {
            $attribute =  $product->getResource()
                ->getAttribute($attributeCode);

            if ($attribute) {
                $attributeLabel = $attribute->getSource()
                    ->getOptionText($product->getData($attributeCode));
            }
            return $attributeLabel;
        }
    }

    public function getConfigurableAttributes()
    {
        $this->_switcher = Mage::getModel('productswitcher/switcher');
        $attribute = $this->_switcher->getDependAttributeObject();


        $dependant = array(
            'attribute'.$attribute->getAttributeId() => array(
                'label'     => $this->_switcher->getDependAttributeLabel(),
                'id'        => $attribute->getAttributeId(),
            ),
        );
        return $dependant;
    }

    /**
     * Get allowed attributes
     *
     * @return array
     */
    public function getAllowAttributes()
    {
        return $this->getProduct()->getTypeInstance(true)
            ->getConfigurableAttributes($this->getProduct());
    }

    public function getColorOptions($optionColors, $mode)
    {

        $data = Mage::getResourceModel('colors/filter_option')->getFilteredOptions($optionColors, $mode);

        return $data;
    }
}