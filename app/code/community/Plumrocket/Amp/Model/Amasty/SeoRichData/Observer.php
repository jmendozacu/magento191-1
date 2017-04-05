<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Amp
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

class Plumrocket_Amp_Model_Amasty_SeoRichData_Observer extends Amasty_SeoRichData_Model_Observer
{

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function sendResponseBefore($observer)
    {
        /**
         * Get pramp helper and check its status
         */
        $helper = Mage::helper('pramp');
        if ($helper->isAmpRequest()) {
            return;
        }

        parent::sendResponseBefore($observer);
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function handleBlockOutput($observer)
    {
        /**
         * Get pramp helper and check its status
         */
        $helper = Mage::helper('pramp');
        if ($helper->isAmpRequest()) {
            return;
        }

        parent::handleBlockOutput($observer);
    }
}
