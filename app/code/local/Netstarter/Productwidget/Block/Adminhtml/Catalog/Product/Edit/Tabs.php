<?php

class Netstarter_Productwidget_Block_Adminhtml_Catalog_Product_Edit_Tabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{

    protected function _prepareLayout()
    {

        $product = $this->getProduct();

        if (!($setId = $product->getAttributeSetId())) {
            $setId = $this->getRequest()->getParam('set', null);
        }

        parent::_prepareLayout();

        $this->addTab('lookbook', array(
            'label' => Mage::helper('catalog')->__('Complete The Look'),
            'url' => $this->getUrl('productwidget/adminhtml_product/look', array('_current' => true)),
            'class' => 'ajax',
        ));
    }

}
