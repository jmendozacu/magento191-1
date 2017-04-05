<?php

/**
 * @category Netstarter
 * @package  Netstarter_Checkout
 * @license  http://netstarter.com.au
 * @link     http://netstarter.com.au
 */
require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'CartController.php';

class Netstarter_Checkout_CartController extends Mage_Checkout_CartController
{

    /**
     * @Method: addAction
     * Add Product to the shopping cart
     *      If Ajax request: send back a json response.
     *      Else redirect accordingly
     * @var nothing
     * @return false
     * Add product to shopping cart action
     */
    public function addAction()
    {
        $params = $this->getRequest()->getParams();
        if ($this->getRequest()->isAjax()) {
            $this->ajaxAddToCart();
        } else {
            $cart = $this->_getCart();           
            try {
                if (isset($params['qty'])) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                            array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $params['qty'] = $filter->filter($params['qty']);
                }

                $product = $this->_initProduct();
                $related = $this->getRequest()->getParam('related_product');

                /**
                 * Check product availability
                 */
                if (!$product) {
                    $this->_goBack();
                    return;
                }

                $cart->addProduct($product, $params);
                if (!empty($related)) {
                    $cart->addProductsByIds(explode(',', $related));
                }

                $cart->save();

                $this->_getSession()->setCartWasUpdated(true);

                /**
                 * @todo remove wishlist observer processAddToCart
                 */
                Mage::dispatchEvent('checkout_cart_add_product_complete', array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );

                if (!$this->_getSession()->getNoCartRedirect(true)) {
                    if (!$cart->getQuote()->getHasError()) {
                        $successMsg = $this->__('%s <em>was added to your shopping cart.</em>', Mage::helper('core')->escapeHtml($product->getName()));
                        $successMsg .= '<a href="'.Mage::helper('checkout/cart')->getCartUrl().'">'.$this->__('View Cart and Checkout').'</a>';
                        //$message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                        $this->_getSession()->addSuccess($successMsg);
                    }
                    $this->_goBack();
                }
            } catch (Mage_Core_Exception $e) {
                if ($this->_getSession()->getUseNotice(true)) {
                    $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                    }
                }

                $url = $this->_getSession()->getRedirectUrl(true);
                if ($url) {
                    $this->getResponse()->setRedirect($url);
                } else {
                    $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
                }
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
                Mage::logException($e);
                $this->_goBack();
            }
        }
    }

    /**
     * Method ajaxAddToCart()
     * Add a product to the cart and send the user json type response (SUCCESS || ERROR)
     * @var nothing
     * @return false : //Send back an ajax json response
     */
    public function ajaxAddToCart()
    {
        $cart = $this->_getCart();
        $params = $this->getRequest()->getParams();
        $response = array();
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $response['status'] = 'ERROR';
                $response['message'] = $this->__('Unable to find Product ID');
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
            array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()){
                    //$message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
                    $successMsg = $this->__('%s <em>added to your cart</em>.', Mage::helper('core')->escapeHtml($product->getName()));
                    $successMsg .= ' <a href="'.Mage::helper('checkout/cart')->getCartUrl().'" onclick="dataLayer.push({\'url\': \'/virtual-page/ecommerce/view-cart/alert-text\', \'pageTitle\': \'Funnel – Checkout Alert\', \'event\': \'virtualpageview\'});">'.$this->__('View Cart and Checkout').'</a>';

                    $response['status'] = 'SUCCESS';
                    $response['message'] = $successMsg;
                    $this->loadLayout();
                    $cartBlock = $this->getLayout()->getBlock('cart_sidebar');
                    $cartBlock->setData('lastAdded', Mage::helper('core')->htmlEscape($product->getName()));
                    $topLink = $cartBlock->toHtml();
                    $response['topCart'] = $topLink;

                    // Irfan modified (2014-03-05)
                    // Mobile site update numbers of items in cart
                    $cartBlockMob = $this->getLayout()->getBlock('cart_sidebar_mob')->toHtml();
                    $response['topCartMob'] = $cartBlockMob;
                    // Ends

                }else{
                    $response['status'] = 'ERROR';
                    $errorList = array();
                    foreach ($cart->getQuote()->getErrors() as $error) {
                        $errorList[] = $error->getCode();
                    }
                    $response['message'] = implode('<br/>', $errorList);
                }
            }
        } catch (Mage_Core_Exception $e) {
            $msg = "";
            if ($this->_getSession()->getUseNotice(true)) {
                $msg = $e->getMessage();
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $msg .= $message.'<br/><br/>';
                }
            }

            $response['status'] = 'ERROR';
            $response['message'] = $msg;
        } catch (Exception $e) {
            $response['status'] = 'ERROR';
            $response['message'] = $this->__('Cannot add the item to shopping cart.');
            Mage::logException($e);
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }

    /**
     * @method  deleteAction - Delete item from shopping cart and send back the new rendered cart block
     * @return  null (Json Encoded Ajax Response)
     * @param   null
     */
    public function deleteAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $response = array();
        if ($id) {
            try {
                $removingItem = $this->_getCart()->getQuote()->getItemById($id);
                $this->_getCart()->removeItem($id)
                    ->save();

                $this->_getSession()->setCartWasUpdated(true);

                /**
                 * @todo remove wishlist observer processAddToCart
                 */
                Mage::dispatchEvent('checkout_cart_remove_product_complete',
                    array('product' => null, 'item' => $removingItem, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );

                if (!$this->_getCart()->getQuote()->getHasError()){
                    //$message = $this->__('Item removed from your shopping cart.');
                    $successMsg = $this->__('Item removed from your shopping cart.');
                    $successMsg .= ' <a href="'.Mage::helper('checkout/cart')->getCartUrl().'">'.$this->__('View Cart and Checkout').'</a>';

                    $response['status'] = 'SUCCESS';
                    $response['message'] = $successMsg;
                    $this->loadLayout();

                    $cartBlock = $this->getLayout()->getBlock('cart_sidebar');
                    if ($removingItem) {
                        $cartBlock->setData('lastRemoved', Mage::helper('core')->escapeHtml($removingItem->getName()));
                    }
                    $topLink = $cartBlock->toHtml();
                    $response['topCart'] = $topLink;

                }
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Cannot remove the item.'));
                Mage::logException($e);
            }
        }

        if ($this->getRequest()->isAjax()) {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        } else {
            $this->_goBack();
        }

        return false;
    }

    /**
     * Handles SaleCycle Request to direct link add to cart
     */
    public function quickaddAction()
    {
        $cartData = $this->getRequest()->getParam('cart');
        $cartUrl = Mage::getUrl('checkout/cart');
        $params = array();

        if ($cartData) {
            $params = $this->_decodeQuickAddParams($cartData);
        } else {
            $this->getResponse()->setRedirect($cartUrl);
        }
        
        $cart = Mage::getSingleton('checkout/cart');

        try {
            if ($params) {
                foreach($params as $productParams) {
                    $product = Mage::getModel('catalog/product')->load($productParams['product']);
                    $cart->addProduct($product, $productParams);
                }
                $cart->save();
                Mage::getSingleton('checkout/session')->setCartWasUpdated(true);

                Mage::dispatchEvent('checkout_cart_add_product_complete', array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );

                if (!$this->_getSession()->getNoCartRedirect(true)) {
                    if (!$cart->getQuote()->getHasError() && !empty($params)) {
                        $successMsg = $this->__('%s <em>product(s) added to your shopping cart.</em>', $params ? count($params) : '');
                        $successMsg .= '&nbsp; <a href="'.Mage::helper('checkout/cart')->getCartUrl().'">'.$this->__('View Cart and Checkout').'</a>';
                        $this->_getSession()->addSuccess($successMsg);
                    }

                    $this->getResponse()->setRedirect($cartUrl);
                }

            } else {
                $this->_getSession()->addNotice($this->__('Cannot add items to shopping cart.'));
            }

            $this->getResponse()->setRedirect($cartUrl);

            /**
             * @todo remove wishlist observer processAddToCart
             */

        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }
            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
        //exit;
    }

    /**
     * Returns the decoded SaleCycle url param into Magento AddCart friendly data array
     * @param $cartData
     * @return array
     */
    protected function _decodeQuickAddParams($cartData)
    {
        $decodedOptions = array();
        $productOptions = explode(',',  $cartData);
        if ($productOptions) {
            foreach ($productOptions as $option) {
                preg_match_all("/\[(.*?)\]/", $option, $matches);
                if (!empty($matches[1])) {
                    list($param['product'], $param['qty'], $param['size'], $param['color']) = array_pad($matches[1], 4, 0);
                    $decodedOptions[] = $param;
                    !empty($param['size']) ? $sizesToFetch[] = $param['size'] : '';
                }
            }

            $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'size');
            if ($attribute->usesSource()) {
                if (!empty($sizesToFetch)) {
                    $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setPositionOrder('asc')
                        ->setAttributeFilter($attribute->getId())
                        ->setStoreFilter($attribute->getStoreId())
                        ->addFieldToFilter('tsv.value', array('in', $sizesToFetch))
                        ->load();

                    if (count($collection)) {
                        foreach ($collection as $option) {
                            $optionMatch[$option['value']] = $option['option_id'];
                        }

                        if ($decodedOptions) {
                            foreach($decodedOptions as $key => $dOption) {
                                if(!empty($optionMatch[$dOption['size']]) ) {
                                    $decodedOptions[$key]['super_attribute'] = array($attribute->getId() => $optionMatch[$dOption['size']]);
                                    unset($decodedOptions[$key]['size']);
                                    unset($decodedOptions[$key]['color']);
                                }
                            }
                        }
                    }//if (count($collection)) {
                }

            }
        }
        return $decodedOptions;
    }
}
