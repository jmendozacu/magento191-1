<?php
class Netstarter_Groupedoptions_Block_Product_View_Option extends Mage_Catalog_Block_Product_View_Options_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        if (!$this->hasData('template')) {
            $this->setTemplate('grouped-options/renderer/default-option.phtml');
        }
    }

    protected function _toHtml() {
        if (($option = $this->getOption()) && ($product = $this->getProduct())
            && !$option->getProduct() ) {
            $option->setProduct($product);
        }

        return parent::_toHtml();
    }

    protected function _beforeToHtml() {
        if (($option = $this->getOption()) != null  && ($product = $this->getProduct()) != null) {
            $option->setProduct($product);
        }
        return parent::_beforeToHtml();
    }
}