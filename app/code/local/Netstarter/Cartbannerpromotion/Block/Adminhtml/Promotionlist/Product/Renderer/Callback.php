<?php
/**
 * Created by JetBrains PhpStorm.
 * User: suresh
 * Date: 8/10/13
 * Time: 7:25 PM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_Cartbannerpromotion_Block_Adminhtml_Promotionlist_Product_Renderer_Callback extends
    Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{

    public function render(Varien_Object $row){
        return $this->getButtonsHtml($row->getId(),$row->getSku(),$row->getName());
    }

    public function getButtonsHtml($productId,$sku,$name)
    {
        $prams = $productId.",'".$sku."','".$name."'";
        $addButtonData = array(
            'label'     => Mage::helper('cartbannerpromotion')->__('Select Product'),
            'onclick'   => 'javascript:setProduct('.$prams.');',
        );
        return $this->getLayout()->createBlock('adminhtml/widget_button')->setData($addButtonData)->toHtml();


    }
}