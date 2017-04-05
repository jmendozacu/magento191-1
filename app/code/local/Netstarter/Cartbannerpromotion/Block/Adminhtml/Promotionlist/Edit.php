<?php
/**
 * Created by PhpStorm.
 * User: Tuan
 * Date: 3/21/14
 * Time: 7:21 PM
 */

class Netstarter_Cartbannerpromotion_Block_Adminhtml_Promotionlist_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'promotion_id';
        $this->_blockGroup = 'cartbannerpromotion';
        $this->_controller = 'adminhtml_promotionlist';

        $this->_updateButton('save', 'label', Mage::helper('cartbannerpromotion')->__('Save Promotion'));
        $this->_addButton('delete', array(
            'label'     => Mage::helper('cartbannerpromotion')->__('Delete Promotion'),
            'class'     => 'delete',
            'onclick'   => 'deleteConfirm(\''. Mage::helper('adminhtml')->__('Are you sure you want to delete this Promotion?')
                .'\', \'' . $this->getUrl('*/*/delete', array('id'=>$this->getRequest()->getParam('id'))) . '\')',
        ));
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        if (Mage::registry('cartbannerpromotion_data') && Mage::registry('cartbannerpromotion_data')->getPromotionId()) {
            return $this->__('Edit Profile');
        }
        else {
            return $this->__('New Profile');
        }
    }
} 