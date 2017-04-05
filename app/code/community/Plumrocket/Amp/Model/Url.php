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

if (Mage::helper('core')->isModuleEnabled('Mana_Seo')) {
    class Plumrocket_Amp_Model_Url_Temporary extends Mana_Seo_Rewrite_Url {}
} else {
    class Plumrocket_Amp_Model_Url_Temporary extends Mage_Core_Model_Url {}
}

class Plumrocket_Amp_Model_Url extends Plumrocket_Amp_Model_Url_Temporary
{
    /**
     * Implement logic of custom rewrites
     *
     * @return string
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        /**
         * Get parrent method
         */
        $url = parent::getUrl($routePath, $routeParams);
        return Mage::helper('pramp')->isAmpRequest() ? Mage::helper('pramp')->getAmpUrl($url) : $url;
    }
}
