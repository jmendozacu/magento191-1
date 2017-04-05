<?php

class Netstarter_Location_Block_Adminhtml_Location_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('location_form', array('legend' => $this->__('Location information')));

        $fieldset->addField('location_id', 'hidden', array(
            'name' => "main[location_id]"
        ));

        $fieldset->addField("name", 'text', array(
            'label' => $this->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => "main[name]",
            'note' => $this->__('Store name displayed in H1 tag')
        ));

        $fieldset->addField('identifier', 'text', array(
            'label' => $this->__('Identifier'),
            'name' => "main[identifier]",
            'note' => $this->__('Url name for the store')
        ));

        $field =$fieldset->addField('store_id', 'multiselect', array(
            'name'      => 'main[stores]',
            'label'     => $this->__('Store View'),
            'title'     => $this->__('Store View'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
        ));
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);

        $fieldset->addField('latitude', 'text', array(
            'label' => $this->__('Latitude'),
            'class'     => 'required-entry',
            'required'  => true,
            'name' => "main[latitude]",
            'note' => $this->__('Format should be -33.8683 (Sydney Latitude)')
        ));

        $fieldset->addField('longitude', 'text', array(
            'label' => $this->__('Longitude'),
            'class'     => 'required-entry',
            'required'  => true,
            'name' => "main[longitude]",
            'note' => $this->__('Format should be 151.2111 (Sydney Longitude)')
        ));

        $fieldset->addField('email', 'text', array(
            'label' => $this->__('Email'),
            'class'     => 'required-entry validate-email',
            'required'  => true,
            'name' => "main[email]"
        ));

        $fieldset->addField('phone', 'text', array(
            'label' => $this->__('Phone'),
            'class'     => 'required-entry',
            'required'  => true,
            'name' => "main[phone]"
        ));

        $fieldset->addField('fax', 'text', array(
            'label' => $this->__('Fax'),
            'name' => "main[fax]"
        ));

        $is_checked= Mage::registry('store_data')->getData('flag');
        $fieldset->addField('flag', 'checkbox', array(
            'label' => $this->__('Is New'),
            'name' => "main[flag]",
            'value' => 1,

        ))->setIsChecked(!empty($is_checked));


        $fieldset->addField('active', 'select', array(
            'label' => $this->__('Status'),
            'name' => "main[active]",
            'values' => array(
                array(
                    'value' => 0,
                    'label' => $this->__('Inactive'),
                ), array(
                    'value' => 1,
                    'label' => $this->__('Active'),
                )
            ),
        ));

        if (Mage::getSingleton('adminhtml/session')->getLocationData()) {

            $data = Mage::getSingleton('adminhtml/session')->getLocationData();
            $form->setValues($data['main']);
        } elseif (Mage::registry('store_data')) {
            $form->setValues(Mage::registry('store_data')->getData());
        }

        return parent::_prepareForm();
    }
}