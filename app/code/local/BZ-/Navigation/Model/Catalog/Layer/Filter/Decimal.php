<?php

/**
 * Class Decimal
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Model_Catalog_Layer_Filter_Decimal extends Mage_Catalog_Model_Layer_Filter_Decimal
{
    protected $_helper;
    protected $_is_search;

    public function __construct()
    {
        parent::__construct();
        $this->_helper = Mage::helper('bz_navigation');
        if(Mage::app()->getRequest()->getModuleName() == 'catalogsearch') $this->_is_search = true;
        else $this->_is_search = false;
    }
    
    public function isSearch(){
        return (bool)$this->_is_search;
    }

    /**
     * Apply decimal range filter to product collection
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Mage_Catalog_Block_Layer_Filter_Decimal $filterBlock
     * @return Mage_Catalog_Model_Layer_Filter_Decimal
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        /**
         * Filter must be string: $from-$to
         */
        $filter = $request->getParam($this->getRequestVar());
        if(!$filter){
            $name = $filterBlock->getName();
            $clean_name = $this->_helper->labelEncode($name);
            $filter = $request->getParam($clean_name);
        }
        if (!$filter) {
            return $this;
        }
        
        $filter = $this->_validateFilter($filter);
        
        if ($filter===false) {
            return $this;
        }

        list($from, $to) = $filter;
        if(empty($from)) $this->setData('current_min',0);
        else $this->setData('current_min',$from);
        if(empty($to)) $this->setData('current_max',0);
        else $this->setData('current_max',$to);
        
        if ((int)$from && (int)$to) {
            $this->_getResource()->applyFilterToCollection($this, $from, $to);
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_renderItemLabel(empty($from) ? 0 : $from, $to), $filter)
            );
            $this->_items = array();
        }

        return $this;
    }
    
    protected function _validateFilter($filter)
    {
        $filter = explode('-', $filter);
        if (count($filter) != 2) {
            return false;
        }
        foreach ($filter as $v) {
            if (($v !== '' && $v !== '0' && (float)$v <= 0) || is_infinite((float)$v)) {
                return false;
            }
        }
        return $filter;
    }
    
    public function getOriginalMaxPirce(){
        $max = $this->getData('org_max_value');
        if (is_null($max)) {
            list($min, $max) = $this->_getResource()->getOrgMaxMin($this);
            $this->setData('org_max_value', $max);
            $this->setData('org_min_value', $min);
        }
        return $max;
    }
    
    public function getOriginalMinPirce(){
        $min= $this->getData('org_min_value');
        if (is_null($min)) {
            list($min, $max) = $this->_getResource()->getOrgMaxMin($this);
            $this->setData('org_max_value', $max);
            $this->setData('org_min_value', $min);
        }
        return $min;
    }
    
    public function getCurrentMaxPrice(){
        if(!is_null($this->getData('current_max'))) return $this->getData('current_max');
        else return 0;
    }
    
    public function getCurrentMinPrice(){
        if(!is_null($this->getData('current_min'))) return $this->getData('current_min');
        else return 0;
    }
    
    protected function _renderItemLabel($from, $to)
    {
        $from = Mage::app()->getStore()->formatPrice($from, false);
        if ($from != $to) {
            $to -= .01;
        }
        $to = Mage::app()->getStore()->formatPrice($to, false);
        return Mage::helper('catalog')->__('%s - %s', $from, $to);
    }
    
    public function getItemsCount(){
        //if selected before then shows
        $params = Mage::app()->getRequest()->getParams();
        $keys = array_keys($params);
        $name = $this->getName();
        $clean_name = $this->_helper->labelEncode($name);
        if(in_array($this->getRequestVar(),$keys) || in_array($clean_name,$keys)) return 1;
        else return parent::getItemsCount();
    }

}
