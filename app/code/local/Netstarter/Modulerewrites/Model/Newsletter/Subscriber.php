<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 10/4/13
 * Time: 4:37 PM
 * To change this template use File | Settings | File Templates.
 */ 
class Netstarter_Modulerewrites_Model_Newsletter_Subscriber extends Mage_Newsletter_Model_Subscriber
{

    /**
     * if the user is already subscribed and confirmed no need send another confirmation
     * email. It sens may emails
     *
     */
    public function subscribe($email)
    {
        $this->loadByEmail($email);
        $customerSession = Mage::getSingleton('customer/session');

        if(!$this->getId()) {
            $this->setSubscriberConfirmCode($this->randomSequence());
        }

        $isConfirmNeed   = (Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_FLAG) == 1) ? true : false;
        $isOwnSubscribes = false;
        $ownerId = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email)
            ->getId();
        $isSubscribeOwnEmail = $customerSession->isLoggedIn() && $ownerId == $customerSession->getId();
        $this->setPreviousStatus($this->getStatus());

        if (!$this->getId() || $this->getStatus() == self::STATUS_UNSUBSCRIBED
            || $this->getStatus() == self::STATUS_NOT_ACTIVE
        ) {
            if ($isConfirmNeed === true) {
                // if user subscribes own login email - confirmation is not needed
                $isOwnSubscribes = $isSubscribeOwnEmail;
                if ($isOwnSubscribes == true){
                    $this->setStatus(self::STATUS_SUBSCRIBED);
                } else {
                    $this->setStatus(self::STATUS_NOT_ACTIVE);
                }
            } else {
                $this->setStatus(self::STATUS_SUBSCRIBED);
                $this->setNewSubscription(true);
            }
            $this->setSubscriberEmail($email);
        }

        if ($isSubscribeOwnEmail) {
            $this->setStoreId($customerSession->getCustomer()->getStoreId());
            $this->setCustomerId($customerSession->getCustomerId());
        } else {
            $this->setStoreId(Mage::app()->getStore()->getId());
            $this->setCustomerId(0);
        }

        $this->setIsStatusChanged(true);

        // Irfan modified (2014-03-05)
        // Subscription Date set using observer

        /**
         * add subscribed date
         */
        // $this->setSubscriptionDate(Mage::getSingleton('core/date')->gmtDate());

        try {
            $this->save();

            /**
             * if the user is already subscribed and confirmed no need send another confirmation email
             *
             */
            if(!($isConfirmNeed && $this->getStatus() == self::STATUS_SUBSCRIBED)){

                if ($isConfirmNeed === true
                    && $isOwnSubscribes === false
                ) {
                   // $this->sendConfirmationRequestEmail();
                } else {
                   // $this->sendConfirmationSuccessEmail();
                }
            }

            return $this->getStatus();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function sendConfirmationSuccessEmail()
    {

    }
    public function sendUnsubscriptionEmail()
    {

    }
    public function sendConfirmationRequestEmail()
    {

    }
}