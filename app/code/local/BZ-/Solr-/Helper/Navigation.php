<?php

/**
 * Class Navigation Description
 * a bridge between bz_navigation and bz_solr if someone get just the solr one
 * @param 
 * @package BZ_Navigation
 * @author Ben Zhang <ben_zhanghf@hotmail.com>
 */
class BZ_Solr_Helper_Navigation extends Mage_Core_Helper_Abstract
{
    protected $_helper = null;

    public function __construct() {
        if($this->isModuleEnabled('BZ_Navigation')){
            $this->_helper = Mage::helper('bz_navigation');
        }
    }

    public function hasNavigationModule(){
        return (bool) $this->_helper;
    }

    public function getFacetSeparator(){
        return '_';
    }

    public function labelEncode($label) {
        if($this->_helper) return $this->_helper->labelEncode($label);
        return urlencode( preg_replace('/[_\/]+/','-',strtolower(trim($label))) );
    }

}
