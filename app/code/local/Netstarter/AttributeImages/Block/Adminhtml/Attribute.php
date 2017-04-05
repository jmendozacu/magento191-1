<?php
/**
 * Created by JetBrains PhpStorm.
 * User: http:://www.netstarter.com.au
 * Date: 3/7/14
 * Time: 7:58 AM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_AttributeImages_Block_Adminhtml_Attribute extends Mage_Adminhtml_Block_Template
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
        $attribute = Mage::getSingleton('eav/config')->getAttribute($this->_scope, $attrName);
        $this->setCurrentAttribute($attribute);
        $options = null;
        if ($attribute && $attribute->usesSource()) {
            $options = $attribute->getSource()->getAllOptions(false);
        }

        $form = new Varien_Data_Form(array(
            'id' => 'attribute_images_form',
            'action' => Mage::getUrl('*/*/save'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));
        $this->setForm($form);

        $fieldset = $form->addFieldset('attribute_option_images_form', array('legend' => $this->__('Attribute Option Images')));

        $fieldset->addField('attcode', 'hidden', array(
            'name' => "attcode",
            'value' => $attrName
        ));


        if ($options) {
            foreach ($options as $option) {
                $optionLabel = $option['label'];
                $optionId = $option['value'];

                $mainImageUrl = $this->helper('attributeimages')->getOptionImageName($attribute->getAttributeCode(), $optionId, $this->_imageExtension);

                $fieldset->addField($optionId, 'image', array(
                    'label'     => $this->__($optionLabel),
                    'required'  => false,
                    'name'      => $optionId,
                    'note'      => $this->__('Max. file size = 500 kb. Only PNGs allowed')
                ))->setAfterElementHtml(($mainImageUrl?"<img src='/media/attributeimages/".$attrName.'/'.$mainImageUrl."' height='auto'/>":''));
            }
        }
        return $this;
    }
}