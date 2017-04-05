<?php
/**
 * FAQ
 * @category   Netstarter
 * @package    Netstarter_Faq
 * @copyright  Copyright (c) 2012 Netstarter
 */
class Netstarter_Faq_Block_Adminhtml_Item_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepares the page layout
     *
     * Loads the WYSIWYG editor on demand if enabled.
     *
     * @return Netstarter_Faq_Block_Admin_Edit
     */
    protected function _prepareLayout()
    {
        $return = parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        return $return;
    }

    /**
     * Preparation of current form
     *
     * @return Netstarter_Faq_Block_Admin_Edit_Tab_Main Self
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('faq');

        try {
            $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
        } catch (Exception $ex) {
            $config = null;
        }

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('faq_');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('netstarter_faq')->__('General information'),
            'class' => 'fieldset-wide'));

        if ($model->getFaqId()) {
            $fieldset->addField('faq_id', 'hidden', array(
                'name' => 'faq_id'));
        }

        $fieldset->addField('question', 'text', array (
            'name' => 'question',
            'label' => Mage::helper('netstarter_faq')->__('FAQ item question'),
            'title' => Mage::helper('netstarter_faq')->__('FAQ item question'),
            'required' => true ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect',
                array(
                    'name' => 'stores[]',
                    'label' => Mage::helper('cms')->__('Store view'),
                    'title' => Mage::helper('cms')->__('Store view'),
                    'required' => true,
                    'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)));
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name' => 'stores[]',
                'value' => Mage::app()->getStore(true)->getId()));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $fieldset->addField('is_active', 'select',
            array(
                'label' => Mage::helper('cms')->__('Status'),
                'title' => Mage::helper('netstarter_faq')->__('Item status'),
                'name' => 'is_active',
                'required' => true,
                'options' => array(
                    '1' => Mage::helper('cms')->__('Enabled'),
                    '0' => Mage::helper('cms')->__('Disabled'))));

        $fieldset->addField('category_id', 'select',
            array(
                'label' => Mage::helper('netstarter_faq')->__('Category'),
                'title' => Mage::helper('netstarter_faq')->__('Category'),
                'name' => 'categories[]',
                'required' => true,
                'values' => Mage::getResourceSingleton('netstarter_faq/category_collection')->toOptionArray(),
            )
        );

        $fieldset->addField('answer', 'editor',
            array(
                'name' => 'answer',
                'label' => Mage::helper('netstarter_faq')->__('Content'),
                'title' => Mage::helper('netstarter_faq')->__('Content'),
                'style' => 'width:700px; height:400px;',
                'config' => $config,
                'required' => true));

        $fieldset->addField('answer_html', 'select',
            array(
                'label' => Mage::helper('netstarter_faq')->__('HTML answer'),
                'title' => Mage::helper('netstarter_faq')->__('HTML answer'),
                'name' => 'answer_html',
                'required' => true,
                'options' => array(
                    '1' => Mage::helper('cms')->__('Enabled'),
                    '0' => Mage::helper('cms')->__('Disabled'))));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
