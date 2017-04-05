<?php

class N2Uri extends N2UriAbstract
{

    private static $uriTranslateFrom = array();
    private static $uriTranslateTo = array();

    function __construct() {
        $this->_baseuri = rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), '/');
        
        self::addTranslate(N2Filesystem::getBasePath(), $this->_baseuri);
        self::addTranslate(Mage::getBaseDir('media'), rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA), '/'));

        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') {
            $this->_baseuri = str_replace('http://', 'https://', $this->_baseuri);
        }
        self::$scheme = parse_url($this->_baseuri, PHP_URL_SCHEME);
    }
    
    private static function addTranslate($path, $uri){
        array_unshift(self::$uriTranslateFrom, $path);
        array_unshift(self::$uriTranslateTo, $uri);
    }

    static function pathToUri($path) {
        $i = N2Uri::getInstance();
        return $i->_baseuri . str_replace(array(
            N2Filesystem::getBasePath(),
            DIRECTORY_SEPARATOR
        ) + self::$uriTranslateFrom, array(
            '',
            '/',
        ) + self::$uriTranslateTo, str_replace('/', DIRECTORY_SEPARATOR, $path));
    }
}