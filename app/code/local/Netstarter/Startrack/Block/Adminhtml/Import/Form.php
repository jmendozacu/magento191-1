<?php

/**
 * Class Netstarter_Startrack_Block_Adminhtml_Import_Form
 *
 * @category  Netstarter
 * @package   Netstarter_Startrack
 *
 * Backend admin form for the input of the Startrack Frightmaster CSV importing.
 * Basically just a file input.
 *
 */
class Netstarter_Startrack_Block_Adminhtml_Import_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $data = array();

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/import'),
            'method' => 'post',
            'target' => '_blank',
            'enctype' => 'multipart/form-data',
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('export_form', array(
            'legend' =>Mage::helper('netstarter_startrack')->__('File')
        ));

        $fieldset->addField('csv_file', 'file', array(
            'name'   => 'csv_file',
            'label'  => Mage::helper('netstarter_startrack')->__('CSV File'),
            'title'  => Mage::helper('netstarter_startrack')->__('CSV File'),
        ));

        $form->setValues($data);

        return parent::_prepareForm();
    }
}
