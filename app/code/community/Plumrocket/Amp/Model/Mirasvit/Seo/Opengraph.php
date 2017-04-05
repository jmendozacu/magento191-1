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

class Plumrocket_Amp_Model_Mirasvit_Seo_Opengraph extends Mirasvit_Seo_Model_Opengraph
{

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     * Rewrite parent method
     */
    public function modifyHtmlResponse($e)
    {
        /**
         * Get pramp helper and check its status
         */
        $helper = Mage::helper('pramp');
        if ($helper->isAmpRequest()) {
            return;
        }

        parent::modifyHtmlResponse($e);
    }


}
