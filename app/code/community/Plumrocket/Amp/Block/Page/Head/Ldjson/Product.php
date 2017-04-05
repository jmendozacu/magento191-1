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

class Plumrocket_Amp_Block_Page_Head_Ldjson_Product extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Default values
     */
    const DEFAULT_PRODUCT_NAME = 'Product name';
    const DEFAULT_PRODUCT_SHORT_DESCRIPTION = 'Product short description';
    const DEFAULT_PRODUCT_STATUS = 'OutStock';
    const DEFAULT_PRODUCT_PRICE_CURRENCY = 'USD';

    const PRODUCT_NAME_MAX_LEN = 32;
    const PRODUCT_SHORT_DESCRIPTION_MAX_LEN = 255;

    const PRODUCT_IMAGE_WIDTH = 720;
    const PRODUCT_IMAGE_HEIGHT = 720;

    /**
     * @return string JSON format according to http://schema.org requirements
     */
    public function getJson()
    {
        /**
         * Get helper, product and store objects
         */
        $_helper = $this->helper('catalog/output');
        $_product = $this->getProduct();
        $_store = $_product->getStore();

        /**
         * Set product default values
         */
        $productName = self::DEFAULT_PRODUCT_NAME;
        $productShortDescription = self::DEFAULT_PRODUCT_SHORT_DESCRIPTION;
        $productStatus = self::DEFAULT_PRODUCT_STATUS;
        $productPrice = 0;
        $productPriceCurrency = self::DEFAULT_PRODUCT_PRICE_CURRENCY;

        /**
         * Set product data from product object
         */
        if ($_product) {
            /**
             * Get product name
             */
            if (strlen($_product->getName())) {
                $productName = $this->escapeHtml(mb_substr($_product->getName(), 0, self::PRODUCT_NAME_MAX_LEN, 'UTF-8'));
            }

            /**
             * Get product image
             */
            $productImage = (string)Mage::helper('catalog/image')->init($_product, 'image')->resize(self::PRODUCT_IMAGE_WIDTH, self::PRODUCT_IMAGE_HEIGHT);

            /**
             * Get product description
             */
            if (strlen($_product->getShortDescription())) {
                $productShortDescription = $this->escapeHtml(mb_substr($_product->getShortDescription(), 0, self::PRODUCT_SHORT_DESCRIPTION_MAX_LEN, 'UTF-8'));
            }
        }

        $siteName = Mage::getStoreConfig('general/store_information/name');
        if (!$siteName) {
            $siteName = 'Magento Store';
        }
        $layout = Mage::app()->getLayout();
        
        $logo = $this->helper('pramp')->getLogoSrc();

        if (!$logo) {
            $header = $layout->getBlock('amp.header');
            $logo = $header ? $header->getLogoSrc() : '';
        }

        $header = $layout->getBlock('amp.header');
        $head = $layout->getBlock('head');

        if($head->getTitle()) {
            $pageContentHeading = $head->getTitle();
        } else {
            $pageContentHeading = $productName;
        }

        $json = array(
            "@context" => "http://schema.org",
            "@type" => "Article",
            "author" => $siteName,
            "image" => array(
                '@type' => 'ImageObject',
                'url' => $productImage,
                'width' => self::PRODUCT_IMAGE_WIDTH,
                'height' => self::PRODUCT_IMAGE_HEIGHT,
            ),
            "name" => $productName,
            "description" => $productShortDescription,
            "datePublished" => $_product->getCreatedAt(),
            "dateModified" => $_product->getUpdatedAt(),
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