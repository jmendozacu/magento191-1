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

class Plumrocket_Amp_Helper_Data extends Plumrocket_Amp_Helper_Main
{
    /**
     * Default value for homepage alias
     */
    const AMP_HOME_PAGE_KEYWORD = 'amphomepage';
    const AMP_FOOTER_LINKS_KEYWORD = 'footer_links_amp';

    const AMP_ROOT_TEMPLATE_NAME_1COLUMN = '1column';
    const AMP_ROOT_TEMPLATE_NAME_OPTIONS = '1column-options';

    protected $_allowedPages;
    protected $_isAmpRequest;
    protected $_ignorePath;
    protected $_needIgnorePath = array(
                '/pramp/product/post',
                '/cart/',
                '/catalogsearch/',
            );

    protected $_isMobile;
    protected $_isTablet;
    protected $_mobileDetected;

    /**
     * Retrieve allowed full action names
     * @param  int $store
     * @return array
     */
    public function getAllowedPages($store = null)
    {
        if ($this->_allowedPages === null) {
            $this->_allowedPages = explode(',', Mage::getStoreConfig('pramp/general/pages', $store));
            if (in_array('catalogsearch_result_index', $this->_allowedPages)) {
                $this->_allowedPages[] = 'pramp_search_index';
            }
            $this->_allowedPages[] = 'turpentine_esi_getBlock';
        }
        return $this->_allowedPages;
    }

    public function isAllowedPage()
    {
        return in_array($this->getFullActionName(), $this->getAllowedPages());
    }

    public function getFullActionName()
    {
        $controller = Mage::app()->getFrontController();
        if ($controller && $controller->getAction()) {
            return $controller->getAction()->getFullActionName();
        }

        $request = Mage::app()->getRequest();
        if ($request) {
            return implode('_', array(
                $request->getModuleName(),
                $request->getControllerName(),
                $request->getActionName(),
            ));
        }

        return '__';
    }

    public function isEsiRequest()
    {
        return $this->getFullActionName() == 'turpentine_esi_getBlock';
    }

    public function isSearchEnabled()
    {
        return in_array('catalogsearch_result_index', $this->getAllowedPages());
    }

    /**
     * @return bool
     * Check magento configuration
     */
    public function moduleEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig('pramp/general/enable', $store);
    }

    public function forceOnMobile($store = null)
    {
        return (bool)Mage::getStoreConfig('pramp/general/force_mobile', $store);
    }

    public function forceOnTablet($store = null)
    {
        return (bool)Mage::getStoreConfig('pramp/general/force_tablet', $store);
    }

    /**
     * @return bool
     * Return true if module enabled and exist request param amp
     */
    public function isAmpRequest()
    {
        if ($this->_isAmpRequest === null) {
            if (!$this->moduleEnabled()) {
                return $this->_isAmpRequest = false;
            }

            if (!$this->isAllowedPage()) {
                if ($this->getFullActionName() == '__') {
                    return false;
                } else {
                    return $this->_isAmpRequest = false;
                }
            }

            if (Mage::app()->getRequest()->getParam('only-options') == 1) {
                return $this->_isAmpRequest = false;
            }

            if (Mage::app()->getRequest()->getParam('noforce') == 1) {
                return false;
            }

            if (Mage::app()->getRequest()->getParam('amp') == 1) {
                return $this->_isAmpRequest = true;
            }

            $forceOnMobile = $this->forceOnMobile();
            if ($forceOnMobile) {
                $forceOnTablet = $this->forceOnTablet();
                $isMobile = $this->isMobile();
                $isTablet = $this->isTablet();
                if ($isMobile && !$isTablet) {
                    $this->_isAmpRequest = true;
                } elseif ($forceOnTablet && $isTablet) {
                    $this->_isAmpRequest = true;
                }
            }
        }
        return $this->_isAmpRequest;
    }

    public function isMobile()
    {
        $this->_detectMobile();
        return $this->_isMobile;
    }

    public function isTablet()
    {
        $this->_detectMobile();
        return $this->_isTablet;
    }

    protected function _detectMobile()
    {
        if (!$this->_mobileDetected) {
            $mobileDetect = new Mobile_Detect();
            $this->_isMobile = $mobileDetect->isMobile();
            $this->_isTablet = $mobileDetect->isTablet();
            $this->_mobileDetected = true;
        }
    }

    public function setAmpRequest($value)
    {
        $this->_isAmpRequest = (bool)$value;
        return $this;
    }

    /**
     * @return bool
     * Return true if module enabled and exist request param only-options
     */
    public function isOnlyOptionsRequest()
    {
        return $this->moduleEnabled()
            && (Mage::app()->getRequest()->getParam('only-options') == 1)
            && ($this->getFullActionName() == 'catalog_product_view');
    }

    public function isLoadOverCdn()
    {
        //return false;
        return (bool)Mage::getStoreConfig('pramp/general/load_over_cdn');
    }

    protected function  getIgnorePath()
    {
        if ($this->_ignorePath === null) {
            $ignoreArray = Mage::getStoreConfig('pramp/general/ignore_page_amp_cdn');
            $ignoreArray = str_replace(array("\n\r", "\r\n","\n"), "\r", $ignoreArray);
            $this->_ignorePath = array_merge(explode("\r", $ignoreArray), $this->_needIgnorePath);
            foreach ($this->_ignorePath as $key => $value) {
                $value = trim($value);
                if (!$value) {
                    unset($this->_ignorePath[$key]);
                }  else {
                    $this->_ignorePath[$key] = $value;
                }
            }
        }
        return $this->_ignorePath;
    }

    protected function isIgnoreUrl($url)
    {
        foreach ($this->getIgnorePath() as $item) {
            if (strpos($url, $item) !== false) {
                return true;
            }
        }
       return false;
    }

    /**
     * @return string
     * url string path
     */
    public function getCurrentPath()
    {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        return  Mage::getSingleton('core/url')->parseUrl($currentUrl)->getPath();
    }

    /**
     * @return string
     * url string without amp parameter
     */
    public function getCanonicalUrl($url = null, $params = null)
    {
        $url = $url ? $url : Mage::helper('core/url')->getCurrentUrl();
        $urlData = parse_url($url);
        $dataQuery = isset($urlData['query']) ? explode('&', $urlData['query']) : array();

        $needSecure = false;
        if (is_array($params) && count($params)) {
            if (isset($params['_secure'])) {
                $needSecure = (bool)$params['_secure'];
                unset($params['_secure']);
            }
            $tempData = array();
            foreach($params as $key => $value) {
                $tempData[] = $key . '=' . $value;
            }
            $dataQuery = array_merge($dataQuery, $tempData);
        }

        foreach ($dataQuery as $key => $value) {
            if (strtolower($value) == 'amp=1') {
                unset($dataQuery[$key]);
            }
        }

        $url = ($needSecure ? 'https' : $urlData['scheme']) . '://' . $urlData['host'] . $urlData['path'];
        if (count($dataQuery)) {
            $url .= '?' . implode('&', $dataQuery);
        }

        if (!empty($urlData['fragment'])) {
            $url .= '#' . $urlData['fragment'];
        }

        return $url;
    }

    /**
     * @return null
     * Set dafault values for module
     */
    public function disableExtension()
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $connection->delete($resource->getTableName('core/config_data'), array($connection->quoteInto('path IN (?)', array('pramp/general/enable'))));
        $config = Mage::getConfig();
        $config->reinit();
        Mage::app()->reinitStores();
    }

    /**
     * @return string
     * url string with amp parameter
     */
    public function getAmpUrl($url = null, $params = null)
    {
        $url = $url ? $url : Mage::helper('core/url')->getCurrentUrl();
        $urlData = parse_url($url);
        $dataQuery = isset($urlData['query']) ? explode('&', $urlData['query']) : array();

        $needSecure = false;
        if (is_array($params) && count($params)) {
            if (isset($params['_secure'])) {
                $needSecure = (bool)$params['_secure'];
                unset($params['_secure']);
            }
            $tempData = array();
            foreach($params as $key => $value) {
                $tempData[] = $key . '=' . $value;
            }
            $dataQuery = array_merge($dataQuery, $tempData);
        }

        if (!in_array('amp=1', $dataQuery)) {
            $dataQuery[] = 'amp=1';
        }

        $_url = $urlData['host'] . $urlData['path'] . '?' . implode('&', $dataQuery);

        if (!empty($urlData['fragment'])) {
            $_url .= '#' . $urlData['fragment'];
        }

        if ($this->isLoadOverCdn() && !$this->isIgnoreUrl($url) && strpos($url, 'cdn.ampproject.org') === false) {
            $url = 'https://cdn.ampproject.org/c/';
            if ($urlData['scheme'] == 'https' || $needSecure) {
                $url .= 's/';
            }
            $url .= $_url;
        } else {
            $url = ($needSecure ? 'https' : $urlData['scheme']) . '://' . $_url;
        }

        return $url;
    }

    /**
     * @var object Mage_Catalog_Model_Product
     * @return string add to cart url
     */
    public function getAddToCartUrl($product)
    {
        return $this->getCanonicalUrl(Mage::getUrl('pramp/cart/add', array('product'=>$product->getId(), '_secure'=>true)));
    }

    /**
     * @var object Mage_Catalog_Model_Product
     * @return string add to cart url
     */
    public function getIframeSrc($product)
    {
        if (!Mage::getStoreConfig('pramp/additional/amp_option_iframe')) {
            return false;
        }

        $secure = Mage::getStoreConfig('web/secure/use_in_frontend');
        $ampIframePath = $this->getAmpIframePath();

        if ($secure && $ampIframePath && ($productUrl = $this->getOnlyOptionsUrl($product))) {
            if (!$this->isTablet()) {
                $ampIframeUrlData = parse_url(Mage::getBaseUrl());
                $prefix = 'www.';
                $ampIframeUrlData['host'] = (strpos($ampIframeUrlData['host'], $prefix) === 0)
                    ? substr($ampIframeUrlData['host'], strlen($prefix))
                    : $prefix . $ampIframeUrlData['host'];

                $productUrl = preg_replace('/\/\/cdn.ampproject.org\/c\/(?:s\/)?/', '//', $productUrl);
                return 'https://' . $ampIframeUrlData['host'] . $ampIframePath . '?referrer=' . base64_encode($productUrl);
            }
        }

        return false;
    }

    public function removeSameOrigin()
    {
        $response = Mage::app()->getResponse();
        $headers = $response->getHeaders();
        $response->clearHeaders();
        foreach($headers as $header) {
            if ($header['name'] !== 'X-Frame-Options') {
                $response->setHeader($header['name'], $header['value'], $header['replace']);
            }
        }
    }

    public function getOnlyOptionsUrl($product)
    {
        if ($product) {
            $productUrl = (!$product->getProductUrl())
            ? Mage::getUrl('catalog/product/view', array('id' => $product->getId()))
            : $product->getProductUrl();

            return $this->getCanonicalUrl($productUrl, array('only-options' => 1, '_secure'=>true));
        }

        return false;
    }

    public function getFormReturnUrl()
    {
        $onlyOptions = 'only-options';
        $params = array(
            '_secure'=>true
        );

        if (!Mage::app()->getRequest()->getParam($onlyOptions)) {
            $params[$onlyOptions] = 1;
        }

        return $this->getCanonicalUrl(null, $params);
    }

    public function isSecure()
    {
        return Mage::app()->getFrontController()->getRequest()->isSecure();
    }

    public function getLogoWidth()
    {
        return (int)Mage::getStoreConfig('pramp/product_page_add_logo/logo_width');
    }

    public function getLogoHeight()
    {
        return (int)Mage::getStoreConfig('pramp/product_page_add_logo/logo_height');
    }

    public function getLogoSrc()
    {
        $logo = Mage::getStoreConfig('pramp/product_page_add_logo/logo');
        if ($logo) {
           $logo = Mage::getBaseUrl('media') . 'pramp/logo/' . $logo;
        }

        return $logo;
    }

    public function getAmpIframePath()
    {
        return  Mage::getStoreConfig('pramp/additional/amp_iframe_path');
    }

    public function getNavigationsTextColor()
    {
        return  Mage::getStoreConfig('pramp/front_design/navigation_menu_text_color');
    }

    public function getLinkColor()
    {
       return  Mage::getStoreConfig('pramp/front_design/link_color');
    }

    public function getLinkColorHover()
    {
        return  Mage::getStoreConfig('pramp/front_design/link_color_hover');
    }

    public function getButtonBgColor()
    {
        return  Mage::getStoreConfig('pramp/front_design/button_bg_color');
    }

    public function getButtonBgColorHover()
    {
        return  Mage::getStoreConfig('pramp/front_design/button_bg_color_hover');
    }

    public function getButtonTextColor()
    {
        return  Mage::getStoreConfig('pramp/front_design/button_text_color');
    }

    public function getButtonTextColorHover()
    {
        return  Mage::getStoreConfig('pramp/front_design/button_text_color_hover');
    }

    public function getPriceTextColor()
    {
        return  Mage::getStoreConfig('pramp/front_design/price_text_color');
    }

    public function getRtlEnabled()
    {
        return  Mage::getStoreConfig('pramp/rtl/enabled');
    }

    /**
     * Escape quotes inside html attributes
     * Use $addSlashes = false for escaping js that inside html attribute (onClick, onSubmit etc)
     *
     * @param string $data
     * @param bool $addSlashes
     * @return string
     */
    public function quoteEscape($data, $addSlashes = false)
    {
        if ($addSlashes === true) {
            $data = addslashes($data);
        }
        return htmlspecialchars($data, ENT_QUOTES, null, false);
    }
}