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
 * @package     Mage_Newsletter
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Newsletter subscribe controller
 *
 * @category    Mage
 * @package     Mage_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once 'Mage'.DS.'Newsletter'.DS.'controllers'.DS.'SubscriberController.php';
class Netstarter_Modulerewrites_Newsletter_SubscriberController extends Mage_Newsletter_SubscriberController
{
    /**
      * New subscription action
      */
    public function newAction()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            //$session            = Mage::getSingleton('core/session');
            $customerSession    = Mage::getSingleton('customer/session');
            $newsletterSession  = Mage::getSingleton('newsletter/session');
            $email              = (string) $this->getRequest()->getPost('email');
            try {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($this->__('Please enter a valid email address.'));
                }

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 && 
                    !$customerSession->isLoggedIn()) {
                    Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                }

                $ownerId = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    Mage::throwException($this->__('This email address is already assigned to another user.'));
                    $newsletterSession->setData('newsletter_subscribe', 'already_assigned');
                }

                $subscription = Mage::getModel('newsletter/subscriber');
                $status = $subscription->subscribe($email);

                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    $newsletterSession->addSuccess($this->__('Thank you, please check your email to confirm.'));
                }
                else {
                    if ($subscription->getNewSubscription()) {
                        $newsletterSession->addSuccess($this->__('Thank you, you are now subscribed.'));
                    } else {
                        $newsletterSession->addSuccess($this->__("Subscription Exist"));
                    }
                    //$newsletterSession->addSuccess('newsletter_subscribe', 'success');

                }
            }
            catch (Mage_Core_Exception $e) {
                //$newsletterSession->addException($e, $this->__('There was a problem with the subscription: %s', $e->getMessage()));
                $newsletterSession->addError($this->__($e->getMessage()));
            }
            catch (Exception $e) {
                $newsletterSession->addError($this->__('There was a problem with the subscription.'));
            }
        }
        $this->_redirectReferer();
    }


    /**
     * Subscription confirm action
     */
    public function confirmAction()
    {
        $id    = (int) $this->getRequest()->getParam('id');
        $code  = (string) $this->getRequest()->getParam('code');

        if ($id && $code) {
            $subscriber = Mage::getModel('newsletter/subscriber')->load($id);
            $session  = Mage::getSingleton('newsletter/session');

            if($subscriber->getId() && $subscriber->getCode()) {
                if($subscriber->confirm($code)) {
                    $session->addSuccess($this->__('Your subscription has been confirmed.'));
                } else {
                    $session->addError($this->__('Invalid subscription confirmation code.'));
                }
            } else {
                $session->addError($this->__('Invalid subscription ID.'));
            }
        }

        $this->_redirectUrl(Mage::getBaseUrl());
    }
}
