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

class Plumrocket_Amp_Model_Observer
{
    public function addDomainPolicyHeader($observer)
    {
        /**
         * Get pramp helper and check its status
         */
        $helper = Mage::helper('pramp');
        if ($helper->moduleEnabled() && $helper->isOnlyOptionsRequest()) {
            if (!$helper->isAllowedPage()) {
                return;
            }
            /**
             * Get response and unset SAMEORIGIN header
             */
            $helper->removeSameOrigin();
        }
    }

    /**
     * @var $observer
     * @return void
     */
    public function controllerActionLayoutLoadBefore($observer)
    {
        /**
         * Get pramp helper and check its status
         */
        $helper = Mage::helper('pramp');
        if (!$helper->moduleEnabled()) {
            return;
        }

        if (Mage::getSingleton('plumbase/observer')->customer() == Mage::getSingleton('plumbase/product')->currentCustomer()) {
            /**
             * Get full action name and update object
             */
            $currentFullAction = $observer->getAction()->getFullActionName();
            $update = $observer->getEvent()->getLayout()->getUpdate();

            if ($helper->isOnlyOptionsRequest()) {
                Mage::app()->getStore()->setConfig('ajaxcart/viewsetting/enableview', 0);
                Mage::app()->getStore()->setConfig('ajaxcart/general/enabled', 0);
                $update->addHandle('amp_catalog_product_view_only_options');
            }

            /**
             * Check get parameter amp
             */
            if ($helper->isAmpRequest()) {
                Mage::app()->getStore()->setConfig('advanced/modules_disable_output/Yireo_GoogleTagManager', 1);
                Mage::app()->getStore()->setConfig('amoptimization/footerjs/enabled', 0);

                if (function_exists('newrelic_disable_autorum')) {
                    newrelic_disable_autorum();
                }

                /**
                 *  Add layout handlers
                 */
                $update->addHandle('amp_default');
                $update->addHandle('amp_' . $currentFullAction);
            }
        }

        /**
         * Add layout changes
         */
        if ($helper->isAllowedPage()) {
            $update = $observer->getEvent()->getLayout()->getUpdate();
            $update->addHandle('amp_non_amp_page');
        }
    }

    /**
     * @var $observer
     * @return void
     */
    public function controllerActionLayoutRenderBefore($observer)
    {
        /**
         * Get pramp helper and check its status
         */
        $helper = Mage::helper('pramp');
        if (!$helper->moduleEnabled()) {
            return;
        }

        $layout = Mage::app()->getLayout();
        /**
         * Set root teplate
         */
        $templateName = false;

        if ($helper->isAmpRequest()) {
            $templateName = Plumrocket_Amp_Helper_Data::AMP_ROOT_TEMPLATE_NAME_1COLUMN;

            /* Do not uncomment. Moved to layout pramp.xml
            if ($helper->getFullActionName() == 'catalog_product_view') {
                $product = Mage::registry('current_product');
                if ($product && strpos($product->getShortDescription(), 'youtube.com/embed') !== false
                    || strpos($product->getDescription(), 'youtube.com/embed') !== false
                ) {
                    if ($ampjsBlock = $layout->getBlock('ampjs')) {
                        $ampjsBlock->addJs('https://cdn.ampproject.org/v0/amp-youtube-0.1.js', 'amp-youtube');
                    }
                }
            }
            */
        }

        if ($helper->isOnlyOptionsRequest()) {
            $templateName = Plumrocket_Amp_Helper_Data::AMP_ROOT_TEMPLATE_NAME_OPTIONS;
        }

        if ($templateName) {
            $layout->getBlock('root')->setTemplate('pramp/' . $templateName . '.phtml');

            $contentBlock = $layout->getBlock('content');
            if ($contentBlock && !$helper->isEsiRequest()) {
                $allowedBlocks = $contentBlock->getAllowedBlocks();
                if ($allowedBlocks) {
                    $allowedBlocks = explode(',', $allowedBlocks);
                } else {
                    $allowedBlocks = array();
                }

                $allowedBlocks = ($templateName == Plumrocket_Amp_Helper_Data::AMP_ROOT_TEMPLATE_NAME_1COLUMN)
                    ? array_merge($allowedBlocks, array('category.products', 'product.info', 'cms.wrapper', 'cms_page', 'page_content_heading', 'search.result'))
                    : array('product.info');

                foreach ($contentBlock->getChild() as $child) {
                    $blockName = $child->getNameInLayout();
                    if (!in_array($blockName, $allowedBlocks)) {
                        $contentBlock->unsetChild($blockName);
                        if ($alias = $child->getBlockAlias()) {
                            $contentBlock->unsetChild($alias);
                        }
                    }
                }
            }
        }
    }

    public function responseSendBefore($observer)
    {
        /**
         * Get pramp helper and check its status
         */
        $helper = Mage::helper('pramp');
        if (!$helper->moduleEnabled()) {
            return;
        }

        if ($helper->isAmpRequest()) {
            $response = $observer->getResponse();
            $html = $response->getBody();

            $html = $this->_replaceHtml($html);

            $response->setBody($html);
        }
    }

    public function coreBlockAbstractToHtmlAfter($observer)
    {
        /**
         * Get pramp helper and check its status
         */
        $helper = Mage::helper('pramp');
        if (!$helper->moduleEnabled()) {
            return;
        }

        if ($helper->isAmpRequest()) {
            $transport = $observer->getTransport();
            $html = $transport->getHtml();

            $html = $this->_replaceHtml($html);

            // remove unused scripts
            if ($observer->getBlock()->getNameInLayout() == 'root') {
                $customElements = array(
                    'carousel',
                    'youtube',
                    'accordion',
                    'iframe',
                ); // don't add "form" and "mustache", because they will be removed later

                foreach ($customElements as $element) {
                    if (strpos($html, '<amp-' . $element) === false) {
                        $html = preg_replace(
                            '/<script async custom-element="amp-' . $element . '".*?><\/script>/is',
                            '',
                            $html
                        );
                    }
                }

                // if <form not found
                if (strpos($html, '<form') === false) {
                    $html = preg_replace(
                        array(
                            '/<script async custom-template="amp-mustache".*?><\/script>/is',
                            '/<script async custom-element="amp-form".*?><\/script>/is',
                        ),
                        '', $html);
                }
            }

            $transport->setHtml($html);
        }
    }

    protected function _replaceHtml($html)
    {
        $html = str_ireplace(
            array('<video','/video>','<audio','/audio>','<ui','/ui>'),
            array('<amp-video','/amp-video>','<amp-audio','/amp-audio>','<ul','/ul>'),
            $html
        );

        $html = preg_replace(
            '/<iframe.+?youtube\.com\/embed\/(.*?)(?:\?|").+?<\/iframe>/s',
            '<amp-youtube data-videoid="$1" layout="responsive" width="480" height="270"></amp-youtube>',
            $html);

        $html = preg_replace(
            '/\s+(?:style|align|hspace|itemprop|itemscope|itemtype|dataurl|onclick|border|vocab|typeof|container|usemap|cellpadding|colspan|rowspan|cellspacing|nowrap)\s*=\s*(?:"[^"]*"|\'[^\']*\')/i',
            '',
            $html); // do not remove "content", "id", "property", "title"

        $html = preg_replace(
            '/(<span[^>]+)(content|property)=(?:"[^"]*"|\'[^\']*\')/',
            '$1',
            $html);

        $html = preg_replace('/<font.*?>(.*?)<\/font>/', '$1', $html);
        $html = preg_replace('/<link[^>]+"http:\/\/purl.org[^"]*"[^\/]*\/>/', '', $html);

        $html =  str_replace(
            array('<link  href="In stock">', 'javascript: void(0)'),
            array('', '#nohref'),
            $html
        );

        $html = preg_replace(
            array(
                '#<script((?!ampproject|application\/ld\+json|application\/json).)*>.*</script>#isU',
                '#<style((?!amp-).)*>.*<\/style>#isU',
                //'#<form.*>.*<\/form>#isU', //need to be for search
                '#<amfpc_ajax.*?\/>#isU', //M1 only
                '#<amfpc_ajax.*>.*<\/amfpc_ajax>#isU', //M1 only
                '#<map.*>.*<\/map>#isU',
                '#<link\s+href="https?:\/\/schema\.org\/[a-zA-Z0-9_\-\/\?\&]*"\s?\/?>#isU',
                '#(?:<col\s+[^>]*(width=(?:"[^"]*"|\'[^\']*\'))[^>]*>)#isU'
            ),
            '', $html);

        $html = preg_replace(
            array(
                '#(<a\s+[^>]*)(alt=(?:"[^"]*"|\'[^\']*\'))([^>]*>)#isU',
                '#(<a\s+[^>]*)(property=(?:"[^"]*"|\'[^\']*\'))([^>]*>)#isU',
                '#(<script\s+[^>]*)(defer\s+)([^>]*>)#isU'
            ),
            '$1$3', $html);

        /* Old replace */
        /*
        $html = preg_replace('/<img(.*?)\/?>/', '<amp-img$1></amp-img>', $html);
        $html = preg_replace('#<amp-img((?(?!width).)*)>\s*</amp-img>#isU', '<amp-img$1 height="100" width="290" ></amp-img>',$html);
        */

        /* Replace width value with data-width-amp value */
        $html = preg_replace('#(<img\s+[^>]*)(?:width=(?:"\w+"|\'\w+\'))([^>]*)(?:data-width-amp="(\w+)")([^>]*>)#isU', '$1 width="$3" $2 $4', $html);
        $html = preg_replace('#(<img\s+[^>]*)(?:height=(?:"\w+"|\'\w+\'))([^>]*)(?:data-height-amp="(\w+)")([^>]*>)#isU', '$1 height="$3" $2 $4', $html);

        /* replace data-width-amp with width */
        $html = preg_replace('#(<img\s+[^>]*)(?:data-width-amp="(\w+)")([^>]*\/?>)#isU', '$1 width="$2" $3', $html);
        $html = preg_replace('#(<img\s+[^>]*)(?:data-height-amp="(\w+)")([^>]*\/?>)#isU', '$1 height="$2" $3', $html);

        /* Add height & width if not exists */
        $html = preg_replace('#(?:<img\s+)((?(?!height=(?:"\w+"|\'\w+\')).)*)(?:\/>|>)#isU', '<img height="100" $1 />', $html);
        $html = preg_replace('#(?:<img\s+)((?(?!width=(?:"\w+"|\'\w+\')).)*)(?:\/>|>)#isU', '<img width="290" $1 />', $html);

        $html = preg_replace('#<img\s+([^>]*)(?:data-src="([^"]*)")([^>]*)\/?>#isU', '<img src="$2" $1 $3/>', $html);

        /* Replace img to amp-img */
        $html = preg_replace('#(?:<img\s+)(.*?)(?:\/>|>)#is', '<amp-img $1></amp-img>', $html);

        return $html;
    }
}