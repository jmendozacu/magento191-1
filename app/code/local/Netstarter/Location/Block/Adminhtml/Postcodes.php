<?php
 
class Netstarter_Location_Block_Adminhtml_Postcodes extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_postcodes';
        $this->_blockGroup = 'location';
        $this->_headerText = Mage::helper('location')->__('Postcodes Manager');
        $this->_addButtonLabel = Mage::helper('location')->__('Add Postcode');

        parent::__construct();
    }
}