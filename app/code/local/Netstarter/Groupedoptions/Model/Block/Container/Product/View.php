<?php
class Netstarter_Groupedoptions_Model_Block_Container_Product_View extends Mage_Catalog_Block_Product_View {

    protected $_product = null;

    protected function _prepareLayout(){}

    public function setProduct(Mage_Catalog_Model_Product $product) {
        $this->_product = $product;
        return $this;
    }

    public function getProduct() {
        return $this->_product;
    }
}