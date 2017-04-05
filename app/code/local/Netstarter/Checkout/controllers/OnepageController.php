<?php
/**
 * @category    design
 * @package     enterprise_bnt
 * @copyright   www.netstarter.com.au
 * @license     www.netstarter.com.au
 */

require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'OnepageController.php';

class Netstarter_Checkout_OnepageController extends Mage_Checkout_OnepageController
{
    public function emailcheckAction() {
        $response = array();

        if ($this->_expireAjax()) {
            return;
        }

        if ($this->getRequest()->isAjax()) {
            if ($this->_expireAjax() || 1) {
                $customerSession = Mage::getSingleton('customer/session');
                if (!$customerSession->isLoggedIn()) {
                    $params = $this->getRequest()->getParam('login');

                    $customer = Mage::getModel('customer/customer');
                    $customer->setWebsiteId(Mage::app()->getWebsite()->getId());

                    $email = !empty($params['username']) ? $params['username'] : '';

                    $emailValidator = new Zend_Validate_EmailAddress();
                    $validEmail = $emailValidator->isValid($email);

                    if (isset($params['username']) &&  $validEmail) {
                        $customer->loadByEmail($email);
                        if ($customer->getId()) {
                            $block = $this->getLayout()->createBlock('nscheckout/onepage_login');
                            $block->setTemplate('checkout/onepage/user_exists.phtml');
                            $block->setCustomer($customer);

                            $response['content'] = $block->toHtml();
                            $response['status'] = 'success';
                        } else {
                            $result = $this->getOnepage()->saveCheckoutMethod('guest');
                            $block = $this->getLayout()->createBlock('nscheckout/onepage_login');
                            $block->setTemplate('checkout/onepage/user_exists.phtml');
                            $response['content'] = $block->toHtml(); // $block->toHtml();
                            $response['status'] = 'email_not_found';
                        }
                    } else {
                        $response['content'] = $this->__('Email Address Cannot be Empty');
                        $response['status'] = 'error';
                        if (!$validEmail) {
                            $messages = $emailValidator->getMessages();
                            $response['messages'] = array_shift($messages);
                        }
                    }
                } else {
                    $response['content'] = ''; //$block->toHtml();
                    $response['status'] = 'logged_in';
                }
            }

        }
        echo Mage::helper('core')->jsonEncode($response);
        die();
    }

    /**
     * save checkout billing address
     */
    public function saveBillingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
//            $postData = $this->getRequest()->getPost('billing', array());
//            $data = $this->_filterPostData($postData);
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
            //Mage::log($data, null, 'RD_Order_Err.log');
            $shippingselection = $data['use_for_shipping'];
            $session = Mage::getSingleton('checkout/session');
            $session->setData('use_for_shipping', $shippingselection);
            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }
            
            $customerSession = Mage::getSingleton('customer/session');
            if (Mage::helper("nscheckout")->isStoreOrder() && !$customerSession->isLoggedIn())
            {
                $pwd = Mage::getModel("customer/customer")->generatePassword();
                $data['customer_password']  = $pwd;
                $data['confirm_password']   = $pwd;
            }

            //Set Checkout method based on the checkbox value
            $createAccount = $this->getRequest()->getParam('billing_create_account');
            if ($createAccount) {
                $this->getOnepage()->getQuote()->setData('checkout_method', Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER);
            } else {
                $this->getOnepage()->getQuote()->setData('checkout_method', Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST);
            }

            if (!empty($data['is_subscribed'])) {
                $this->getOnepage()->getQuote()->setData('customer_newslettersubscribed', 1);
            }

            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

            if (!isset($result['error'])) {
                /* check quote for virtual */
                if ($this->getOnepage()->getQuote()->isVirtual()) {
                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                        'name' => 'payment-method',
                        'html' => $this->_getPaymentMethodsHtml()
                    );
                } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                    $result['goto_section'] = 'shipping_method';
                    $result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                    );

                    $result['allow_sections'] = array('shipping');
                    $result['duplicateBillingInfo'] = 'true';
                } else {
                    $result['goto_section'] = 'shipping';
                }
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Save payment ajax action
     *
     * Sets either redirect or a JSON response
     */
    public function savePaymentAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        try {
            if (!$this->getRequest()->isPost()) {
                $this->_ajaxRedirectResponse();
                return;
            }

            // set payment to quote
            $result = array();
            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->getOnepage()->savePayment($data);

            // get section and redirect data
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
		        $this->saveOrderAction();
                return;
               // $this->_forward('saveOrder');
            }
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set Payment Method.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

   /**
     * Create order action
     */
    public function saveOrderAction()
    {

        $result = array();
        try {
            if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }
            if ($data = $this->getRequest()->getPost('payment', false)) {
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }
            $this->getOnepage()->saveOrder();

            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error']   = false;
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if( !empty($message) ) {
                $result['error_messages'] = $message;
            }
            $result['goto_section'] = 'payment';
            $result['update_section'] = array(
                'name' => 'payment-method',
                'html' => $this->_getPaymentMethodsHtml()
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            if ($gotoSection = $this->getOnepage()->getCheckout()->getGotoSection()) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()->getCheckout()->setGotoSection(null);
            }

            if ($updateSection = $this->getOnepage()->getCheckout()->getUpdateSection()) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                        'name' => $updateSection,
                        'html' => $this->$updateSectionFunction()
                    );
                }
                $this->getOnepage()->getCheckout()->setUpdateSection(null);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }
        $this->getOnepage()->getQuote()->save();
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    public function saveShippingAction()
    {
       // Mage::log('Netstarter Save shipping Action', null, 'RD_Order_Err.log');
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping', array());
            $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
            $result = $this->getOnepage()->saveShipping($data, $customerAddressId);
            if (!isset($result['error'])) {
                $session = Mage::getSingleton('checkout/session');
                $use_for_shipping = $session->getData('use_for_shipping');
                if($use_for_shipping==2){
                    $shipping_method = "clickncollect_clickncollect";
                    $result = $this->getOnepage()->saveShippingMethod($shipping_method);
                    /*
                    $result will have erro data if shipping method is empty
                    */
                    if(!$result) {
                        Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
                            array('request'=>$this->getRequest(),
                                'quote'=>$this->getOnepage()->getQuote()));
                        $this->getOnepage()->getQuote()->collectTotals();
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                        $result['goto_section'] = 'payment';
                        $result['update_section'] = array(
                            'name' => 'payment-method',
                            'html' => $this->_getPaymentMethodsHtml()
                        );
                    }
                    $this->getOnepage()->getQuote()->collectTotals()->save();
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                }
                else {
                    $result['goto_section'] = 'shipping_method';
                    $result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                    );
                }
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
    /**
     * Order success action
     */
    public function successAction()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }

        $order = Mage::getModel('sales/order')->load($lastOrderId);
        if ($order) {
            Mage::register('current_order', $order);
        } else {
            $this->_redirect('checkout/cart');
            return;
        }

        $session->clear();
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
        $this->renderLayout();
    }
}
