<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 9/9/13
 * Time: 8:01 AM
 * To change this template use File | Settings | File Templates.
 */

class Netstarter_Modulerewrites_Model_Newsletter_Observer
{

    /**
     * Address Newsletter Subscribe
     *
     * @category Netstarter
     * @package  Netstarter_Modulerewrites
     * @author   http://www.netstarter.com.au/
     * @license  http://www.netstarter.com.au/license.txt
     * @link     N/A
     */

    public function saveNewsletterSubscriberObserve(Varien_Event_Observer $observer)
    {
        try{
            /**
             * add subscribed date
             */
            $observer->getEvent()->getData('subscriber')->setSubscriptionDate(Mage::getSingleton('core/date')->gmtDate());

        }catch (Exception $e){
            Mage::logException($e);
        }

       return $observer;
    }



}