<?php
class Netstarter_Groupedoptions_Model_Block_Container_Product_View_Options extends Mage_Catalog_Block_Product_View_Options
{
    protected function _construct()
    {

    }

    public function getOptions($product = null)
    {
        if ($product == null) {
            $product = $this->getProduct();
        }

        $options = array();
        foreach ($product->getProductOptionsCollection() as $option) {
            $option->setProduct($product);
            $options[] = $option;
        }

        return $options;
    }
}