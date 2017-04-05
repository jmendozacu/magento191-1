<?php
 
class Netstarter_Afeature_Block_Adminhtml_Afeature_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
               
        $this->_objectId = 'id';
        $this->_blockGroup = 'afeature';
        $this->_controller = 'adminhtml_afeature';
 
        $this->_updateButton('save', 'label', Mage::helper('afeature')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('afeature')->__('Delete Item'));
    }
 
    public function getHeaderText()
    {
        if( Mage::registry('afeature_data') && Mage::registry('afeature_data')->getId() ) {
            return Mage::helper('afeature')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('afeature_data')->getTitle()));
        } else {
            return Mage::helper('afeature')->__('Add Item');
        }
    }
}