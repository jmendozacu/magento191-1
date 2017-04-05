<?php
 
class Netstarter_Location_Block_Adminhtml_Location_Edit_Tab_Store extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('location_form_store', array('legend'=> $this->__('Store information'),'class' => 'fieldset-wide'));


        $fieldset->addField('info_id', 'hidden', array(
            'name' => "store[info_id]"
        ));

        $mainImageUrl = Mage::registry('info_data')->getImagePath();

        $fieldset->addField('store_image', 'image', array(
            'label'     => $this->__('Image'),
            'required'  => false,
            'name'      => 'store_image',
            'note'      => $this->__('Max. file size = 500 kb. Only PNG, JPG, JPEG types are allowed')
        ))->setAfterElementHtml(($mainImageUrl?"<img src='/media/location/".$mainImageUrl."' height='auto' width='100px'/>":''));

        $fieldset->addField('hours', 'editor', array(
            'label'     => $this->__('Hours'),
            'name'      => "store[hours]",
            'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(array('add_variables' => false,
                'add_widgets' => false,'files_browser_window_url'=>Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'))),
            'wysiwyg' => true,
            'style' => 'height:15em !important',
        ));

        $fieldset->addField('address', 'editor', array(
            'label'     => $this->__('Address'),
            'name'      => "store[address]",
            'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(array('add_variables' => false,
                'add_widgets' => false,'files_browser_window_url'=>Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'))),
            'wysiwyg' => true,
            'style' => 'height:15em !important',
        ));

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