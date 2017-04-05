<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Catalog product controller
 * @namespace  Netstarter
 * @modul      Netstarter_Quickview
 * @category   Mage
 * @package    Mage_Adminhtml
 * @date       Jun 19, 2013
 */

class Netstarter_Quickview_ProductController extends Mage_Core_Controller_Front_Action
{
    /**
     * If isAjax request ? render the quick view : redirect to the product page
     * Quickinfo for Product
     */
    public function indexAction()
    {
        $productId = $this->getRequest()->getParam('productId');

        if($productId){

            $viewHelper = Mage::helper('catalog/product_view');

            $params = new Varien_Object();

            if (!($this->getRequest()->isXmlHttpRequest())) {
                $activeProduct = Mage::getModel('catalog/product')->load($productId);

                if ($activeProduct instanceof Mage_Catalog_Model_Product) {
                    $this->_redirectUrl($activeProduct->getProductUrl());
                } else {
                    $this->_forward('defaultNoRoute');
                }
                return $this;
            }

            // Render page
            try {
                $viewHelper->prepareAndRender($productId, $this, $params);

                $this->getResponse()->clearHeaders()->setBody($this->getLayout()->getBlock('content')->toHtml());
            } catch (Exception $e) {

                Mage::logException($e);
            }
        }
    }

    public function groupAction()
    {
        $productId = $this->getRequest()->getParam('productId');

        if($productId){

            $viewHelper = Mage::helper('catalog/product_view');

            $params = new Varien_Object();

            if (!($this->getRequest()->isXmlHttpRequest())) {
                $activeProduct = Mage::getModel('catalog/product')->load($productId);

                if ($activeProduct instanceof Mage_Catalog_Model_Product) {
                    $this->_redirectUrl($activeProduct->getProductUrl());
                } else {
                    $this->_forward('defaultNoRoute');
                }
                return $this;
            }

            // Render page
            try {
                $viewHelper->prepareAndRender($productId, $this, $params);

                $viewBlock = $this->getLayout()->getBlock('quickview_product_view');

                $related = $this->getRequest()->getParam('related');

                if(!empty($related)){
                    $viewBlock->setFilters($related);
                }
                $this->getResponse()->clearHeaders()->setBody($viewBlock->toHtml());

            } catch (Exception $e) {

                Mage::logException($e);
            }
        }
    }
}
