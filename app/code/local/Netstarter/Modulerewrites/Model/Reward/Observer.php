<?php
/**
 * Class Netstarter_Modulerewrites_Model_Reward_Observer
 */
class Netstarter_Modulerewrites_Model_Reward_Observer extends Enterprise_Reward_Model_Observer
{


    /**
     * Overridden to add an exception handler, because sometime we get sql error on order save
     *
     * Update points balance after first successful subscribtion
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function customerSubscribed($observer)
    {
        $customerId = null;
        try{

            /* @var $subscriber Mage_Newsletter_Model_Subscriber */
            $subscriber = $observer->getEvent()->getSubscriber();
            // reward only new subscribtions
            if (!$subscriber->isObjectNew() || !$subscriber->getCustomerId()) {
                return $this;
            }
            $websiteId = Mage::app()->getStore($subscriber->getStoreId())->getWebsiteId();
            if (!Mage::helper('enterprise_reward')->isEnabledOnFront($websiteId)) {
                return $this;
            }

            $customerId = $subscriber->getCustomerId();

            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomerId($subscriber->getCustomerId())
                ->setStore($subscriber->getStoreId())
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_NEWSLETTER)
                ->setActionEntity($subscriber)
                ->updateRewardPoints();

        }catch (Exception $e){
            /**
             * @todo  remove the debug code
             */

            Mage::log($customerId, null, 'subscriber_exp.log');

            Mage::logException($e);
        }

        return $this;
    }
}
