<?php
 
class Netstarter_Location_Block_Adminhtml_Location_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'location_form';
        $this->_blockGroup = 'location';
        $this->_controller = 'adminhtml_location';
        $this->_updateButton('save', 'label', Mage::helper('location')->__('Save Store'));
        $this->_updateButton('delete', 'label', Mage::helper('location')->__('Delete Store'));
    }

    public function getHeaderText()
    {
        return Mage::helper('location')->__('Add Location');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }
}