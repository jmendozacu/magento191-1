<?php
class Netstarter_StockAlerts_Block_Form extends Mage_Core_Block_Template
{
    protected $_siblingProducts = null;
    protected $_outStockSizes = null;
    protected $_outStockColorSizes = null;
    protected $_product;
    protected $_message;

    public function getProduct()
    {
        if ($this->_product){
            return $this->_product;
        } else {
            return $this->loadProduct();
        }
    }

    public function setMessage($type, $msg) {
        $this->_message[$type] = $msg;
    }

    public function getMessage($type=null) {
        if (isset($this->_message[$type])) {
            return $this->_message[$type];
        } else {
            return false;
        }
    }

    public function loadProduct() {
        $productId = $this->getRequest()->getParam('productId');
        $product = Mage::getModel('catalog/product')->load($productId);
        if ($product instanceof Mage_Catalog_Model_Product) {
            return $product;
        } else {
            return false;
        }
    }

    public function isStockNotifyEnabled() {
        return Mage::getStoreConfig('catalog/productalert/allow_stock');
    }


    public function getOutStockColors($itemCode=NULL)
    {
        if (!$itemCode) {
            $itemCode = $this->getProduct()->getItemCode();
        }
        $outStockColors = array();
        $outStockSizeJson = '';
        if ($itemCode) {
            $products = $this->getSiblingProductsByItemCode($itemCode);
            if (count($products)) {
                $configType = Mage::getModel('catalog/product_type_configurable');
                foreach ($products as $product) {
                    switch ($product->getTypeId()) {
                        case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE:
                                if (!$product->isSaleable() && $product->getColor()) {
                                    $outStockColors[$product->getId()] = $product->getAttributeText('color');
                                    $outStockSizeJson[$product->getId()][$product->getId()] = ($product->getAttributeText('size')) ? $product->getAttributeText('size') : $this->__('Any Size');
                                }
                            break;
                        case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                            if (!$product->isSaleable() && $product->getIsNoncore()) {
                                break; // All child products are outof stock for non-core product - not visible in frontend
                            }

                            $configType->setProduct($product);
                            $children =  $configType->getUsedProductCollection()->addAttributeToSelect(array('size', 'is_noncore'));

                            if ($children) {
                                foreach ($children as $child) {

                                    if (!$child->isSaleable() && $product->getColor() && !$child->getIsNoncore()) {
                                        if($product->getAttributeText('color')){
                                            $outStockColors[$product->getId()] = $product->getAttributeText('color');
                                        }else{
                                            $outStockColors[$product->getId()] = $this->__('No Colour Available');
                                        }

                                        $outStockSizeJson[$product->getId()][$child->getId()] = $child->getAttributeText('size');
                                    }
                                }
                            }
                            break;
                    }
                }
            }
        }
        $this->_outStockColorSizes = $outStockSizeJson;
        return $outStockColors;
    }

    public function getSiblingProductsByItemCode($itemCode)
    {
        $storeId = Mage::app()->getStore()->getId();

        $products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(array('name','color', 'price','tax_class_id','final_price','simple_color','is_noncore'))
            ->addStoreFilter($storeId)
            ->addFieldToFilter('status',Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->addFieldToFilter('item_code', $this->getProduct()->getItemCode());

        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        return $products;
    }

    public function getJsonOutStockSizes () {
        return $this->_outStockColorSizes;
    }
}