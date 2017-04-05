<?php
 
class Netstarter_Location_Block_Adminhtml_Postcodes_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'postcodes_form';
        $this->_blockGroup = 'location';
        $this->_controller = 'adminhtml_postcodes';
        $this->_updateButton('save', 'label', Mage::helper('location')->__('Save Postcode'));
        $this->_updateButton('delete', 'label', Mage::helper('location')->__('Delete Postcode'));
    }

    public function getHeaderText()
    {
        return Mage::helper('location')->__('Add Location');
    }
}