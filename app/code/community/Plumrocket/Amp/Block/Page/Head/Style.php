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

class Plumrocket_Amp_Block_Page_Head_Style extends Mage_Core_Block_Template
{
    public function getAmpSkinUrl($file = null, array $params = array())
    {
        $url = $this->getSkinUrl($file, $params);
        $fontInfo = parse_url($url);
        $baseInfo = parse_url(Mage::getBaseUrl());
        $url = str_replace($fontInfo['host'], $baseInfo['host'], $url);

        return $url;
    }

    /* Minify css */
    protected function _toHtml()
    {
        $html = parent::_toHtml();

        if ($html) {
            $html = str_replace(
                array(' {', "}\n"),
                array('{', '}'),
                $html
            );
        }

        return $html;
    }
}