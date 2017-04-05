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


class Plumrocket_Amp_Block_Page_Head_Og_Category extends Mage_Core_Block_Template
{
    public function getOgParams()
    {
        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $_category = Mage::registry('current_category');
            $thumb = $_category->getThumbnail();

            return array(
                'type' => 'category',
                'title' => $head->getTitle(),
                'url' => $this->helper('pramp')->getCanonicalUrl($_category->getUrl()),
                'description' => mb_substr($head->getDescription(), 0, 200, 'UTF-8'),
                'image' => $thumb ? Mage::getBaseUrl('media') . 'catalog/category/' . $thumb : '',
            );
        }

        return array();
    }
}