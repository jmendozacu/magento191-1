<?php
 
class Netstarter_Afeature_Block_Adminhtml_Afeature_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
 
    public function __construct()
    {
        parent::__construct();
        $this->setId('afeature_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('afeature')->__('Afeature Information'));
    }
 
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('afeature')->__('Item Information'),
            'title'     => Mage::helper('afeature')->__('Item Information'),
            'content'   => $this->getLayout()->createBlock('afeature/adminhtml_afeature_edit_tab_form')->toHtml(),
        ));
    
        return parent::_beforeToHtml();
    }
}