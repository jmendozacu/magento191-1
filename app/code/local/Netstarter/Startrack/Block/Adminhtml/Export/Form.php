<?php

/**
 * Class Netstarter_Startrack_Block_Adminhtml_Export_Form
 *
 * @category  Netstarter
 * @package   Netstarter_Startrack
 *
 * Backend admin form for the input of the Startrack Frightmaster CSV generation.
 *
 */
class Netstarter_Startrack_Block_Adminhtml_Export_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $data = array();

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/export'),
            'method' => 'post',
            'target' => '_blank',
            'enctype' => 'multipart/form-data',
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('export_form', array(
            'legend' =>Mage::helper('netstarter_startrack')->__('Date Range')
        ));
        
//        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_FULL);
//        $fieldset->addField('from_date', 'date', array(
//            'name'   => 'from_date',
//            'label'  => Mage::helper('netstarter_startrack')->__('From Date'),
//            'title'  => Mage::helper('netstarter_startrack')->__('From Date'),
//            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
//            'input_format' => Varien_Date::DATETIME_INTERNAL_FORMAT,
//            'format'       => 'dd/MM/yyyy HH:mm:ss',
//            'time'  => true
//        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date', 'date', array(
            'name'   => 'from_date',
            'label'  => Mage::helper('netstarter_startrack')->__('From Date'),
            'title'  => Mage::helper('netstarter_startrack')->__('From Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));
        $fieldset->addField('to_date', 'date', array(
            'name'   => 'to_date',
            'label'  => Mage::helper('netstarter_startrack')->__('To Date'),
            'title'  => Mage::helper('netstarter_startrack')->__('To Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));

        $form->setValues($data);

        return parent::_prepareForm();
    }
}
