<?php
/**
 * FAQ
 * @category   Netstarter
 * @package    Netstarter_Faq
 * @copyright  Copyright (c) 2012 Netstarter
 */
class Netstarter_Faq_Block_Adminhtml_Item_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	/**
	 * Constructor for the FAQ edit form
	 *
	 */
	public function __construct()
	{
		$this->_objectId = 'faq_id';
        $this->_blockGroup = 'netstarter_faq';
		$this->_controller = 'adminhtml_item';
		
		parent::__construct();
		
		$this->_updateButton('save', 'label', Mage::helper('netstarter_faq')->__('Save FAQ item'));
		$this->_updateButton('delete', 'label', Mage::helper('netstarter_faq')->__('Delete FAQ item'));
		
		$this->_addButton('saveandcontinue', array (
				'label' => Mage::helper('netstarter_faq')->__('Save and continue edit'),
				'onclick' => 'saveAndContinueEdit()', 
				'class' => 'save' ), -100);
		
		$this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
	}
	
	/**
	 * Helper function to edit the header of the current form
	 *
	 * @return string Returns an "edit" or "new" text depending on the type of modifications.
	 */
	public function getHeaderText()
	{
		if (Mage::registry('faq')->getFaqId()) {
			return Mage::helper('netstarter_faq')->__("Edit FAQ item '%s'", $this->htmlEscape(Mage::registry('faq')->getQuestion()));
		}
		else {
			return Mage::helper('netstarter_faq')->__('New FAQ item');
		}
	}
	
	public function getFormActionUrl()
	{
        return $this->getUrl('*/faq/save');
    }

    /**
     * Returns the CSS class for the header
     * 
     * Usually 'icon-head' and a more precise class is returned. We return
     * only an empty string to avoid spacing on the left of the header as we
     * don't have an icon.
     * 
     * @return string
     */
    public function getHeaderCssClass()
    {
        return '';
    }
}
