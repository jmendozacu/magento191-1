<?php
/**
 * Created by PhpStorm.
 * User: Tuan
 * Date: 3/21/14
 * Time: 7:22 PM
 */

class Netstarter_Cartbannerpromotion_Block_Adminhtml_Promotionlist_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('promotion_form');
        $this->setTitle($this->__('Cart Promotion Information'));
    }
    protected function _prepareForm()
    {
        if (Mage::registry('cartbannerpromotion_data'))
        {
            $data = Mage::registry('cartbannerpromotion_data')->getData();
        }
        else
        {
            $data = array();
        }
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ));

        $fieldset = $form->addFieldset('cartbannerpromotion_form', array(
            'legend' =>Mage::helper('cartbannerpromotion')->__('Cart Promotion Information')
        ));

        $fieldset->addField('promotion_name', 'text', array(
            'label'     => Mage::helper('cartbannerpromotion')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'promotion_name',
            'note'     => Mage::helper('cartbannerpromotion')->__('The name Promotion.'),
        ));

        $fieldset->addField('promotion_text', 'text', array(
            'label'     => Mage::helper('cartbannerpromotion')->__('Promotion Text'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'promotion_text',
        ));

        $fieldset->addField('product_id', 'hidden', array(
            'name'      => 'product_id',
            'label'     => Mage::helper('cartbannerpromotion')->__('product  id'),
            'title'     => Mage::helper('cartbannerpromotion')->__('productid'),
            'disabled'  => false
        ));

        $fieldset->addField('product_name', 'text', array(
            'name'      => 'product_name',
            'readonly'  => true,
            'label'     => Mage::helper('cartbannerpromotion')->__('Product Name'),
            'title'     => Mage::helper('cartbannerpromotion')->__('Product Name'),
            'after_element_html' => $this->getLinkHtml(),
            'required'  => true,
        ));
        $mainImageUrl = Mage::registry('cartbannerpromotion_data')->getBanner();
        $fieldset->addField('banner', 'image', array(
            'label'     => $this->__('Upload Banner'),
            'required'  => false,
            'name'      => 'banner',
            'note'      => $this->__('Max. file size = 500 kb. Only PNG, JPG, JPEG types are allowed')
        ))->setAfterElementHtml(($mainImageUrl?"<img src='/media/cartbanner/".$mainImageUrl."' height='auto' width='100px'/>":''));;

        if (isset($_FILES['banner']['name']) && $_FILES['filename']['name'] != '') {
            /*
                Move uploaded file logic when user select a image
            */

        } else {

            if(isset($data['banner']['delete']) && $data['banner']['delete'] == 1) {
                /*
                    When user click on checkbox for deletion
                */
                $data['banner'] = '';
            } else {
                /*
                    in edit mode when user nothing did with image not
                    select for deletion nor selected new image then you must
                    be remove element from data so magento will ignore image
                    field and this issue will be resolved
                */
                unset($data['banner']);
            }
        }


        $fieldset->addField('promotion_start', 'date', array(
            'name'      => 'promotion_start',
            'label'     => Mage::helper('cartbannerpromotion')->__('Start Date'),
            'title'     => Mage::helper('cartbannerpromotion')->__('Start Date'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        ));
        $fieldset->addField('promotion_end', 'date', array(
            'name'      => 'promotion_end',
            'label'     => Mage::helper('cartbannerpromotion')->__('End Date'),
            'title'     => Mage::helper('cartbannerpromotion')->__('End Date'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        ));

        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('cartbannerpromotion')->__('Status'),
            'name'      => 'status',
            'required'  => true,
            'values'    => array(
                Netstarter_Cartbannerpromotion_Model_Promotionlist::STATUS_ENABLED  => Mage::helper('cartbannerpromotion')->__('Active'),
                Netstarter_Cartbannerpromotion_Model_Promotionlist::STATUS_DISABLED  => Mage::helper('cartbannerpromotion')->__('Inactive')


            ),
            'value' => Netstarter_Cartbannerpromotion_Model_Promotionlist::STATUS_ENABLED,
        ));

        $form->setValues($data);
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    public function getLinkHtml()
    {
        $popupUrl = $this->getUrl('cartbannerpromotion/adminhtml_promotionlist/productgrid');
        return  '<a href="javascript:openPopup(\''.$popupUrl.'\', \'Product Popup\')">'.Mage::helper('cartbannerpromotion')->__('Load Product').'</a>';

    }
} 