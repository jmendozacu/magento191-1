<?php

/**
 * Multiple wishlist frontend search controller
 *
 * @category    Netstarter
 * @package     Netstarter_Modulerewrites
 */

require_once 'Enterprise/Wishlist/controllers/IndexController.php';

class Netstarter_Modulerewrites_Wishlist_IndexController extends Enterprise_Wishlist_IndexController
{

    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->getWishlistErrorUrl() && $this->getRequest()->getParam('come_back')) {
            Mage::getSingleton('customer/session')->setWishlistErrorUrl($this->_getRefererUrl());
        }
    }

    /**
     * Add item to wishlist
     * Create new wishlist if wishlist params (name, visibility) are provided
     */
    public function addAction()
    {
        $customerId = $this->_getSession()->getCustomerId();
        $name = $this->getRequest()->getParam('name');
        $visibility = ($this->getRequest()->getParam('visibility', 0) === 'on' ? 1 : 0);
        if ($name !== null) {
            try {
                $wishlist = $this->_editWishlist($customerId, $name, $visibility);
                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_wishlist')->__('Wishlist "%s" was successfully saved', Mage::helper('core')->escapeHtml($wishlist->getName()))
                );
                $this->getRequest()->setParam('wishlist_id', $wishlist->getId());
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect($this->_getRefererUrl());
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e,
                    Mage::helper('enterprise_wishlist')->__('Error happened during wishlist creation')
                );
                $this->_redirect($this->_getRefererUrl());
            }
        }


        //parent::addAction();

        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return $this->norouteAction();
        }

        $session = Mage::getSingleton('customer/session');

        $productId = (int) $this->getRequest()->getParam('product');
        if (!$productId) {
            $this->_redirect('*/');
            return;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $session->addError($this->__('Cannot specify product.'));
            $this->_redirect('*/');
            return;
        }

        try {
            $requestParams = $this->getRequest()->getParams();
            if ($session->getBeforeWishlistRequest()) {
                $requestParams = $session->getBeforeWishlistRequest();
                $session->unsBeforeWishlistRequest();
            }
            $buyRequest = new Varien_Object($requestParams);

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                Mage::throwException($result);
            }
            $wishlist->save();

            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist'  => $wishlist,
                    'product'   => $product,
                    'item'      => $result
                )
            );

            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            } else {
                $referer = $this->_getRefererUrl();
            }

            /**
             *  Set referer to avoid referring to the compare popup window
             */
            $session->setAddActionReferer($referer);

            Mage::helper('wishlist')->calculate();

            $message = $this->__('%1$s has been added to your wishlist. Click <a href="%2$s">here</a> to continue shopping.', $product->getName(), Mage::helper('core')->escapeUrl($referer));
            $session->addSuccess($message);
        }
        catch (Mage_Core_Exception $e) {
            if ($customUrl = Mage::getSingleton('customer/session')->getWishlistErrorUrl()) {
                $coreSession = Mage::getSingleton('core/session');
                $coreSession->addError($this->__('An error occurred while adding item to wishlist: %s', $e->getMessage()));
                //reset the go back wish list url and redirect
                Mage::getSingleton('customer/session')->setWishlistErrorUrl();
                $this->getResponse()->setRedirect($customUrl);
                return $this;
            } else {
                $session->addError($this->__('An error occurred while adding item to wishlist: %s', $e->getMessage()));
            }
        }
        catch (Exception $e) {
            if ($customUrl = Mage::getSingleton('customer/session')->getWishlistErrorUrl()) {
                $coreSession = Mage::getSingleton('core/session');
                $coreSession->addError($this->__('An error occurred while adding item to wishlist: %s', $e->getMessage()));
                //reset the go back wish list url and redirect
                Mage::getSingleton('customer/session')->setWishlistErrorUrl();
                $this->getResponse()->setRedirect($customUrl);
                return $this;
            } else {
                $session->addError($this->__('An error occurred while adding item to wishlist.'));
            }
        }
        $this->_redirect('*', array('wishlist_id' => $wishlist->getId()));
    }
}
