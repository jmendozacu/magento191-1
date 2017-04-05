<?php
/**
 *
 */

require_once 'Mage/Catalog/controllers/Product/CompareController.php';

class Netstarter_Extcatalog_Product_CompareController extends Mage_Catalog_Product_CompareController
{

    public function ajaxAddToCompare()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        $response = null;

        if ($productId
            && (Mage::getSingleton('log/visitor')->getId() || Mage::getSingleton('customer/session')->isLoggedIn())
        ) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            if ($product->getId()/* && !$product->isSuper()*/) {
                Mage::getSingleton('catalog/product_compare_list')->addProduct($product);
                Mage::getSingleton('catalog/session')->setCatalogCompareItemsCount(true);
                Mage::helper('catalog/product_compare')->calculate();
                $successMsg = $this->__('The product %s has been added to comparison list.', Mage::helper('core')->escapeHtml($product->getName()));

                $response['status'] = 'SUCCESS';
                $response['message'] = $successMsg;
                $this->loadLayout();
                $compareBlock = $this->getLayout()->createBlock('catalog/product_compare_sidebar')->setTemplate('catalog/product/compare/sidebar.phtml');
                $response['compare'] = $compareBlock->toHtml();

                Mage::dispatchEvent('catalog_product_compare_add_product', array('product'=>$product));
            }else{
                $response['status'] = 'ERROR';
                $response['message'] = $this->__('The product could not be found.');
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }
    /**
     * Add item to compare list
     */
    public function addAction()
    {

        if ($this->getRequest()->isAjax()) {
            $this->ajaxAddToCompare();
        } else {

            $productId = (int) $this->getRequest()->getParam('product');
            if ($productId
                && (Mage::getSingleton('log/visitor')->getId() || Mage::getSingleton('customer/session')->isLoggedIn())
            ) {
                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);

                if ($product->getId()/* && !$product->isSuper()*/) {
                    Mage::getSingleton('catalog/product_compare_list')->addProduct($product);
                    Mage::getSingleton('catalog/session')->addSuccess(
                        $this->__('The product %s has been added to comparison list.', Mage::helper('core')->escapeHtml($product->getName()))
                    );
                    Mage::dispatchEvent('catalog_product_compare_add_product', array('product'=>$product));
                }

                Mage::helper('catalog/product_compare')->calculate();
            }
            $this->_redirectReferer();
        }

    }

    public function updateAction()
    {

        $switchCompare = $this->getRequest()->getParam('switch');

        if ($beforeUrl = $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
            Mage::getSingleton('catalog/session')
                ->setBeforeCompareUrl(Mage::helper('core')->urlDecode($beforeUrl));
        }

        if (!empty($switchCompare)) {

            $list = Mage::getSingleton('catalog/product_compare_list');

            if (count($list)) {
                $list->updateProduct($switchCompare['removed'], $switchCompare['selected']);
            }
        }

        $this->_redirect('*/*/index');
    }

    public function ajaxRemoveCompare()
    {
        $response = null;

        if ($productId = (int) $this->getRequest()->getParam('product')) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            if ($product->getId()) {

                $item = Mage::getModel('catalog/product_compare_item');
                if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $item->addCustomerData(Mage::getSingleton('customer/session')->getCustomer());
                } elseif ($this->_customerId) {
                    $item->addCustomerData(
                        Mage::getModel('customer/customer')->load($this->_customerId)
                    );
                } else {
                    $item->addVisitorId(Mage::getSingleton('log/visitor')->getId());
                }

                $item->loadByProduct($product);

                if($item->getId()) {
                    $item->delete();

                    $successMsg = $this->__('The product %s has been removed from comparison list.', Mage::helper('core')->escapeHtml($product->getName()));
                    Mage::dispatchEvent('catalog_product_compare_remove_product', array('product'=>$item));
                    Mage::helper('catalog/product_compare')->calculate();

                    $response['status'] = 'SUCCESS';
                    $response['message'] = $successMsg;
                    $this->loadLayout();
                    $compareBlock = $this->getLayout()->createBlock('catalog/product_compare_sidebar')->setTemplate('catalog/product/compare/sidebar.phtml');
                    $response['compare'] = $compareBlock->toHtml();

                }else{
                    $response['status'] = 'ERROR';
                    $response['message'] = $this->__('The product could not be found.');
                }

            }else{
                $response['status'] = 'ERROR';
                $response['message'] = $this->__('The product could not be found.');
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }

    /**
     * Remove item from compare list
     */
    public function removeAction()
    {
        if ($this->getRequest()->isAjax()) {
            $this->ajaxRemoveCompare();
        } else {

            if ($productId = (int) $this->getRequest()->getParam('product')) {
                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);

                if($product->getId()) {
                    /** @var $item Mage_Catalog_Model_Product_Compare_Item */
                    $item = Mage::getModel('catalog/product_compare_item');
                    if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                        $item->addCustomerData(Mage::getSingleton('customer/session')->getCustomer());
                    } elseif ($this->_customerId) {
                        $item->addCustomerData(
                            Mage::getModel('customer/customer')->load($this->_customerId)
                        );
                    } else {
                        $item->addVisitorId(Mage::getSingleton('log/visitor')->getId());
                    }

                    $item->loadByProduct($product);

                    if($item->getId()) {
                        $item->delete();
                        Mage::getSingleton('catalog/session')->addSuccess(
                            $this->__('The product %s has been removed from comparison list.', $product->getName())
                        );
                        Mage::dispatchEvent('catalog_product_compare_remove_product', array('product'=>$item));
                        Mage::helper('catalog/product_compare')->calculate();
                    }
                }
            }

            $this->_redirectReferer();
        }
    }

    /**
     * Remove all items from comparison list
     */
    public function clearAction()
    {

        if ($this->getRequest()->isAjax()) {
            $this->ajaxClearCompare();
        } else {
            $items = Mage::getResourceModel('catalog/product_compare_item_collection');

            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $items->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
            } elseif ($this->_customerId) {
                $items->setCustomerId($this->_customerId);
            } else {
                $items->setVisitorId(Mage::getSingleton('log/visitor')->getId());
            }

            /** @var $session Mage_Catalog_Model_Session */
            $session = Mage::getSingleton('catalog/session');

            try {
                $items->clear();
                $session->addSuccess($this->__('The comparison list was cleared.'));
                Mage::helper('catalog/product_compare')->calculate();
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, $this->__('An error occurred while clearing comparison list.'));
            }

            $this->_redirectReferer();
        }
    }

    /**
     * Handling Ajax Requested Clear request
     */
    public function ajaxClearCompare()
    {
        $response = null;

        $items = Mage::getResourceModel('catalog/product_compare_item_collection');

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $items->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
        } elseif ($this->_customerId) {
            $items->setCustomerId($this->_customerId);
        } else {
            $items->setVisitorId(Mage::getSingleton('log/visitor')->getId());
        }

        /** @var $session Mage_Catalog_Model_Session */

        try {
            $items->clear();
            $successMsg = 'The comparison list was cleared.';

            $response['status'] = 'SUCCESS';
            $response['message'] = $successMsg;
            $this->loadLayout();

            Mage::helper('catalog/product_compare')->calculate();
        } catch (Mage_Core_Exception $e) {
            $response['status'] = 'ERROR';
            $response['message'] = $this->__($e->getMessage());
        } catch (Exception $e) {
            $response['status'] = 'ERROR';
            $response['message'] = $this->__('An error occurred while clearing comparison list.');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }

        /**
     * Setter for customer id
     *
     * @param int $id
     * @return Mage_Catalog_Product_CompareController
     */
    public function setCustomerId($id)
    {
        $this->_customerId = $id;
        return $this;
    }
}
