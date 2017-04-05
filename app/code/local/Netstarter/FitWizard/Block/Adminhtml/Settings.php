<?php
/**
 * Created by JetBrains PhpStorm.
 * User: http:://www.netstarter.com.au
 * Date: 3/7/14
 * Time: 7:58 AM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_FitWizard_Block_Adminhtml_Settings extends Mage_Adminhtml_Block_Template
{
    protected $_formInit = false;
    protected $_scope = 'catalog_product';
    protected $_form = null;
    protected $_imageExtension = '.png';

    /**
     * Prepare the form
     * @param string $attribute
     * @return mixed
     */
    public function getFormHtml($attribute = 'features') {
        if (!$this->_formInit) {
            $this->prepareForm($attribute);
        }
        $this->_form->setUseContainer(true);
        return  $this->_form->getHtml();
    }

    /**
     * setter
     * @param $form
     */
    public function setForm($form) {
        $this->_form = $form;
    }

    /**
     * @param $attrName
     * @return $this
     */
    public function prepareForm($attrName) {
        $form = new Varien_Data_Form(array(
            'id' => 'fitwizard_settings_upload',
            'action' => Mage::getUrl('*/*/save'),
            'name' => 'fitwizard_settings_upload',
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));
        $this->setForm($form);

        $fieldset = $form->addFieldset('fitwizard_settins_upload_form', array('legend' => $this->__('Bra Finder Logic')));

        $fieldset->addField('attcode', 'hidden', array(
            'name' => "attcode",
            'value' => $attrName
        ));

        $afterHtml = '';
        $existingFile = $this->helper('fitwizard')->getExistingLogicFile();
        if ($existingFile) {
            $afterHtml = '<a href="'.$existingFile.'">'.$this->__('Download Existing Logic File').'</a>';
        }


        $fieldset->addField('logic_upload', 'file', array(
            'label'     => $this->__('Upload Category Mapping Logic'),
            'required'  => false,
            'name'      => 'logic_upload',
            'note'      => $this->__('Please upload the logic table required for Bra Finder (Current Logic table can be downloaded')
        ))->setAfterElementHtml($afterHtml);

        return $this;
    }
}