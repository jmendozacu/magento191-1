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

class Plumrocket_Amp_Block_Page_Head_Ldjson_Cms extends Mage_Core_Block_Template
{
	const DEFAULT_PAGE_TITLE = "Magento Cms Page";
	const DEFAULT_PAGE_CONTENT_HEADING = "Page Content Heading";
    const DEFAULT_PAGE_DESCRIPTION = "Default Description";

    const LOGO_IMAGE_WIDTH = 272;
    const LOGO_IMAGE_HEIGHT = 90;

    /**
     * @return string JSON format according to http://schema.org requirements
     */
    public function getJson()
    {
    	$siteName = Mage::getStoreConfig('general/store_information/name');
        if (!$siteName) {
            $siteName = 'Magento Store';
        }
    	$layout = Mage::app()->getLayout();

        $head = $layout->getBlock('head');
    	$cmsPage = Mage::getSingleton('cms/page');

        $logo = $this->helper('pramp')->getLogoSrc();
        if (!$logo) {
            $header = $layout->getBlock('amp.header');
            $logo = $header ? $header->getLogoSrc() : '';
        }

    	$pageTitle = $cmsPage->getTitle() ? $cmsPage->getTitle() : self::DEFAULT_PAGE_TITLE;
    	$pageCreatedAt = $cmsPage->getCreationTime() ? $cmsPage->getCreationTime() : '';
        if (!$pageCreatedAt) {
            $date = (string)Mage::getConfig()->getNode('global/install/date');
            $pageCreatedAt = date('c', strtotime($date));
        }
    	$pageUpdatedAt = $cmsPage->getUpdateTime() ? $cmsPage->getUpdateTime() : '';
        if (!$pageUpdatedAt) {
            $date = Mage::getModel('core/date')->date('Y-m') . '-01';
            $pageUpdatedAt = date('c', strtotime($date));
        }

        $pageDescription = $head->getDescription() ? mb_substr($head->getDescription(), 0, 250, 'UTF-8') : self::DEFAULT_PAGE_DESCRIPTION;

    	if ($cmsPage->getContentHeading()) {
    		$pageContentHeading = $cmsPage->getContentHeading();
		} elseif($head->getTitle()) {
			$pageContentHeading = $head->getTitle();
		} else {
			$pageContentHeading = self::DEFAULT_PAGE_CONTENT_HEADING;
    	}

        $json = array(
            "@context" => "http://schema.org",
            "@type" => "Article",
            "author" => $siteName,
            "image" => array(
                '@type' => 'ImageObject',
                'url' => $logo,
                'width' => 696,
                'height' => self::LOGO_IMAGE_HEIGHT,
            ),
            "name" => $pageTitle,
            "description" => $pageDescription,
            "datePublished" => $pageCreatedAt,
            "dateModified" => $pageUpdatedAt,
            "headline" => mb_substr($pageContentHeading, 0, 110, 'UTF-8'),
            "publisher" => array(
            	'@type' => 'Organization',
            	'name' => $siteName,
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => $logo,
                ),
            ),
            "mainEntityOfPage" => array(
                "@type" => "WebPage",
                "@id" => Mage::getBaseUrl(),
            ),
        );

        return str_replace('\/', '/', json_encode($json));
    }

}