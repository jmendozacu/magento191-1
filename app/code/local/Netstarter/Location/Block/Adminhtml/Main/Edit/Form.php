<?php

class Netstarter_Location_Block_Adminhtml_Main_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save'),
                'method' => 'post',
                'enctype' => 'multipart/form-data')
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('location_form_store', array('legend'=> $this->__('Store information'),'class' => 'fieldset-wide'));


        $fieldset->addField('description', 'editor', array(
            'label'     => $this->__('Description'),
            'name'      => "store[description]",
            'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(array('add_variables' => false,
                'add_widgets' => false,'files_browser_window_url'=>Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'))),
            'wysiwyg' => true,
            'style' => 'height:15em !important',
        ));


        $fieldsetMeta = $form->addFieldset('location_form_meta', array('legend'=> $this->__('Meta Data'),'class' => 'fieldset-wide'));

        $fieldsetMeta->addField('meta_title', 'text', array(
            'label'     => $this->__('Meta Title'),
            'name'      => "store[meta_title]"
        ));

        $fieldsetMeta->addField('meta_description', 'textarea', array(
            'label'     => $this->__('Meta Description'),
            'name'      => "store[meta_description]"
        ));

        $fieldsetMeta->addField('meta_keywords', 'textarea', array(
            'label'     => $this->__('Meta Keywords'),
            'name'      => "store[meta_keywords]"
        ));

        if ( Mage::getSingleton('adminhtml/session')->getLocationData() ){

            $data = Mage::getSingleton('adminhtml/session')->getLocationData();
            $form->setValues($data['store']);
            Mage::getSingleton('adminhtml/session')->setLocationData(null);
        } elseif ( Mage::registry('info_data') ) {

            $form->setValues(Mage::registry('info_data')->getData());
        }

        return parent::_prepareForm();
    }
}