<?php
 
class Netstarter_Location_Block_Adminhtml_Location extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_location';
        $this->_blockGroup = 'location';
        $this->_headerText = Mage::helper('location')->__('Stores Manager');
        $this->_addButtonLabel = Mage::helper('location')->__('Add Store');

        parent::__construct();
    }
}