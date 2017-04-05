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

class Plumrocket_Amp_Block_Page_Head_Ldjson_Category extends Mage_Core_Block_Template
{
    const DEFAULT_CATEGORY_NAME = 'Magento Category Name';
    const DEFAULT_CATEGORY_DESCRIPTION = 'Magento Category Description';

    const LOGO_IMAGE_WIDTH = 272;
    const LOGO_IMAGE_HEIGHT = 90;

    const DEFAULT_THUMB_WIDTH = 696;
    const DEFAULT_THUMB_HEIGHT = 696;

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
        
        $logo = $this->helper('pramp')->getLogoSrc();
        if (!$logo) {
            $header = $layout->getBlock('amp.header');
            $logo = $header ? $header->getLogoSrc() : '';
        }
        

        $currentCategory = $this->getCurrentCategory();

        $categoryName = $currentCategory->getName() ? $currentCategory->getName() : self::DEFAULT_CATEGORY_NAME;
        $categoryDescription = $head->getDescription() ? mb_substr($head->getDescription(), 0, 250, 'UTF-8') : self::DEFAULT_CATEGORY_DESCRIPTION;
    	$categoryCreatedAt = $currentCategory->getCreatedAt() ? $currentCategory->getCreatedAt() : '';
    	$categoryUpdatedAt = $currentCategory->getUpdatedAt() ? $currentCategory->getUpdatedAt() : '';

		if($head->getTitle()) {
			$pageContentHeading = $head->getTitle();
		} else {
			$pageContentHeading = $categoryName;
    	}

		$categoryThumb = $currentCategory->getThumbnail() ? Mage::getBaseUrl('media') . 'catalog/category/' . $currentCategory->getThumbnail() : false;
		if($categoryThumb) {
			$dataImageObject = array(
                '@type' => 'ImageObject',
                'url' => $categoryThumb,
                'width' => self::DEFAULT_THUMB_WIDTH,
                'height' => self::DEFAULT_THUMB_HEIGHT,
			);
		} else {
			$dataImageObject = array(
                '@type' => 'ImageObject',
                'url' => $logo,
                'width' => 696,
                'height' => self::LOGO_IMAGE_HEIGHT,
			);
		}

        /**
         * Set scheme JSON data
         */
        $json = array(
            "@context" => "http://schema.org",
            "@type" => "Article",
            "author" => $siteName,
            "image" => $dataImageObject,
            "name" => $categoryName,
            "description" => $categoryDescription,
            "datePublished" => $categoryCreatedAt,
            "dateModified" => $categoryUpdatedAt,
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

    /**
     * Retrieve current category model object
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', Mage::registry('current_category'));
        }
        return $this->getData('current_category');
    }
}