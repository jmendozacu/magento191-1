<?php

class Netstarter_Location_Block_Adminhtml_Postcodes_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form   = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('postcodes_form', array('legend' => $this->__('Postcode information')));

        $model = Mage::registry('postcode_data');

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name'      => 'id',
            ));
        }

        $options = Mage::getSingleton('directory/country')->getResourceCollection()->toOptionArray();

        $fieldset->addField("countrycode", 'select', array(
            'label' => $this->__('Country Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'countrycode',
            'values'    => $options,
        ));

        $fieldset->addField('postcode', 'text', array(
            'label' => $this->__('Postcode'),
            'name' => "postcode",
        ));


        $fieldset->addField('suburb', 'text', array(
            'label' => $this->__('Suburb'),
            'name' => 'suburb',
        ));

        $fieldset->addField('state', 'text', array(
            'label' => $this->__('State'),
            'name' => 'state',
        ));

        $fieldset->addField('statecode', 'text', array(
            'label' => $this->__('State Code'),
            'name' => "statecode",
        ));

        $fieldset->addField('city', 'text', array(
            'label' => $this->__('City'),
            'required' => true,
            'name' => "city",
        ));

        $fieldset->addField('latitude', 'text', array(
            'label' => $this->__('Latitude'),
            'class'     => 'required-entry',
            'required'  => true,
            'name' => 'latitude',
            'note' => $this->__('Format should be -33.8683 (Sydney Latitude)')
        ));

        $fieldset->addField('longitude', 'text', array(
            'label' => $this->__('Longitude'),
            'class'     => 'required-entry',
            'required'  => true,
            'name' => 'longitude',
            'note' => $this->__('Format should be 151.2111 (Sydney Longitude)')
        ));


        if (Mage::getSingleton('adminhtml/session')->getPostcodeData()) {

            $data = Mage::getSingleton('adminhtml/session')->getPostcodeData();
            $form->setValues($data);
        } elseif (Mage::registry('postcode_data')) {
            $form->setValues($model->getData());
        }

        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}