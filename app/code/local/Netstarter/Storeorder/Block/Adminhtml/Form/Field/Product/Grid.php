<?php

class Netstarter_Storeorder_Block_Adminhtml_Form_Field_Product_Grid extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn('product_sku', array(
            'label' => $this->__('Product SKU'),
            'style' => 'width:100px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = $this->__('Add Try Befor You Buy Product');
    }

}
