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

class Plumrocket_Amp_Model_Url_Rewrite_Request extends Mage_Core_Model_Url_Rewrite_Request
{
    /**
     * Rewrite parent method for
     * Add location header and disable browser page caching
     *
     * @param string $url
     * @param bool $isPermanent
     */
    protected function _sendRedirectHeaders($url, $isPermanent = false)
    {
        if (Mage::app()->getRequest()->getParam('amp') == 1) { // change condition, don't need other checks
            $url = preg_replace('/\??amp=1/isU', '', $url);
            $symbol = (strpos($url, '?') === false) ? '?' : '&';
            $url .= $symbol . 'amp=1';
        }

        parent::_sendRedirectHeaders($url, $isPermanent);
    }

}