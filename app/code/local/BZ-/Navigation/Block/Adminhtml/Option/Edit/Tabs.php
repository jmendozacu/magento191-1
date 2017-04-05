<?php
/**
 * Class Tabs
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Block_Adminhtml_Option_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('bz_navigation_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('bz_navigation')->__('Filter Information'));
    }
    
    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label'     => Mage::helper('bz_navigation')->__('Filter Settings'),
            'title'     => Mage::helper('bz_navigation')->__('Filter Settings'),
            'content'   => $this->getLayout()->createBlock('bz_navigation/adminhtml_option_edit_tab_general')->toHtml()
        ));

        $this->addTab('options', array(
            'label'     => Mage::helper('bz_navigation')->__('Label / Options / Images'),
            'title'     => Mage::helper('bz_navigation')->__('Label / Options / Images'),
            'content'   => $this->getLayout()->createBlock('bz_navigation/adminhtml_option_edit_tab_options')->toHtml()
        ));
        
        $this->_updateActiveTab();

        return parent::_beforeToHtml();
    }
    
    protected function _updateActiveTab()
    {
        $page = $this->getRequest()->getParam('page',false);
        $filter = $this->getRequest()->getParam('filter',false);
        $sort = $this->getRequest()->getParam('sort',false);
        $limit = $this->getRequest()->getParam('limit',false);
        if( $filter!==false || $sort!==false || $limit!==false || $page!==false) {
            $this->setActiveTab('options');
        }
        else{
            $this->setActiveTab('general');
        }
    }
    
}
