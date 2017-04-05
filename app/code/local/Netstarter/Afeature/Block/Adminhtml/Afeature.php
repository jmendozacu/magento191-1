<?php
 
class Netstarter_Afeature_Block_Adminhtml_Afeature extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_afeature';
        $this->_blockGroup = 'afeature';
        $this->_headerText = Mage::helper('afeature')->__('Item Manager');
        $this->_addButtonLabel = Mage::helper('afeature')->__('Add Item');
        parent::__construct();
    }
}