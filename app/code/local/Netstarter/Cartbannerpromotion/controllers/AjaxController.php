<?php
/**
 * Created by PhpStorm.
 * User: Tuan
 * Date: 3/26/14
 * Time: 12:14 PM
 */

class Netstarter_Cartbannerpromotion_AjaxController extends Mage_Core_Controller_Front_Action {

    public function selectAction(){
        $id = trim($this->getRequest()->getParam('send-id'));

        if($id){
            $product = Mage::getModel('catalog/product')->load($id);
            Mage::register('product', $product);
            $this->loadLayout()->getLayout()->getBlock('content');
            $cmsBlock = $this->getLayout()
                ->createBlock('productswitcher/switcher', 'productswithcer.options.promotion.cart.product', null)
                ->setTemplate('cartbannerpromotion/switcher.phtml');

            $optionsConfigurable = $this->getLayout()
                ->createBlock('extcatalog/product_view_type_configurable', 'productswithcer.options.promotion.cart.configurable', null)
                ->setTemplate('cartbannerpromotion/configurable_category.phtml');

            $groupedoptions = $this->getLayout()
                ->createBlock('groupedoptions/product_view_options', 'groupedoptions', null)
                ;

            $optionsgrouped = $this->getLayout()
                ->createBlock('catalog/product_view_type_grouped', 'productswithcer.options.optionsgrouped', null)
                ->setTemplate('cartbannerpromotion/configurable_category.phtml')->append($groupedoptions);

            $listBlock = $this->getLayout()
                ->createBlock('cartbannerpromotion/cart', 'productswithcer.options.promotion.cart', null)->append($cmsBlock)->append($optionsConfigurable)->append($optionsgrouped);

            $listView['content'] = $listBlock->setTemplate('cartbannerpromotion/cart.phtml')->toHtml();
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($listView));
        }

    }
}
