<?php
/**
 * Class General
 *
 * @author bzhang@netstarter.com.au
 */
class Netstarter_Colors_Block_Adminhtml_Option_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
        implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {

    }

    public function getTabLabel(){
        //return Mage::helper('ns_navigation')->__('Filter Settings');
    }

    public function getTabTitle(){
        //return Mage::helper('ns_navigation')->__('Filter Settings');
    }

    public function canShowTab(){
        //return true;
    }

    public function isHidden(){
        //return false;
    }
}
