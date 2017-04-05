<?php
/**
 * FAQ
 * @category   Netstarter
 * @package    Netstarter_Faq
 * @copyright  Copyright (c) 2012 Netstarter
 */
class Netstarter_Faq_Block_Adminhtml_Item_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Constructs current object
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('faq_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('netstarter_faq')->__('FAQ item information'));
    }
    
    /**
     * Prepares the page layout
     * 
     * Adds the tabs to the left tab menu.
     * 
     * @return Netstarter_Faq_Block_Admin_Edit
     */
    protected function _prepareLayout()
    {
        $return = parent::_prepareLayout();

        $this->addTab(
            'main_section', 
            array(
                'label' => Mage::helper('netstarter_faq')->__('General information'),
                'title' => Mage::helper('netstarter_faq')->__('General information'),
                'content' => $this->getLayout()->createBlock('netstarter_faq/adminhtml_item_edit_tab_main')->toHtml(),
                'active' => true,
            )
        );
        
        return $return;
    }
}
