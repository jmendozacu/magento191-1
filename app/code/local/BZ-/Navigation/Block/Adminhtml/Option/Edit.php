<?php
/**
 * edit page for attribute option with image uploader
 * @author Ben Zhang <bzhang@netstarter.com.au>
 */

class BZ_Navigation_Block_Adminhtml_Option_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct() {
        parent::__construct();
        $this->_objectId = 'attribute_id';
        $this->_blockGroup = 'bz_navigation';
        $this->_controller = 'adminhtml_option';
        $this->_removeButton('reset');
        $this->_removeButton('delete');
        $this->_updateButton('save', 'label', Mage::helper('adminhtml')->__('Save Filter'));
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save and Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
        
    }

    public function getHeaderText() {
        if (Mage::registry('attribute_model')->getId()) {
            return Mage::helper('adminhtml')->__("Edit Filter '%s'", $this->escapeHtml(Mage::registry('attribute_model')->getFrontendLabel()));
        }
        else {
            return Mage::helper('adminhtml')->__('Edit Filter');
        }
    }
}
