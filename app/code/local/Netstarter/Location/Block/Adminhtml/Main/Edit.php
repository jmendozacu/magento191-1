<?php
 
class Netstarter_Location_Block_Adminhtml_Main_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'location_form';
        $this->_blockGroup = 'location';
        $this->_controller = 'adminhtml_main';

        $this->_updateButton('save', 'label', $this->__('Save Main Page'));
        $this->_removeButton('back');
    }

    public function getHeaderText()
    {
        return $this->__('Edit Main Page');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }
}