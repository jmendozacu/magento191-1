<?php
/**
 * Class Price
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Model_Catalog_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price
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
    
    public function getCurrentMaxPrice(){
        if(!is_null($this->getData('current_max'))) return $this->getData('current_max');
        else return 0;
    }
    
    public function getCurrentMinPrice(){
        if(!is_null($this->getData('current_min'))) return $this->getData('current_min');
        else return 0;
    }
    
    public function getOriginalMaxPirce(){
        if($this->_is_search) return $this->_getSearchMaxPrice();
        $collection = $this->getData('org_collection');
        if(is_null($collection)){
            $collection = $this->getLayer()->getCurrentCategory()->getProductCollection();
            $collection->addMinimalPrice()
                    ->addFinalPrice()
                    ->addTaxPercents();
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
            $this->setData('org_collection',$collection);
        }
        return $collection->getMaxPrice()+1;
    }
    
    protected function _getSearchMaxPrice(){
        //since ee1.12+
        $maxPrice = $this->getLayer()->getProductCollection()->getMaxPrice();
        return ceil($maxPrice+1);
    }

    public function getOriginalMinPirce(){
        if($this->_is_search) return $this->_getSearchMinPrice();
        $collection = $this->getData('org_collection');
        if(is_null($collection)){
            $collection = $this->getLayer()->getCurrentCategory()->getProductCollection();
            $collection->addMinimalPrice()
                    ->addFinalPrice()
                    ->addTaxPercents();
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
            $this->setData('org_collection',$collection);
        }
        return $collection->getMinPrice();
    }
    
    protected function _getSearchMinPrice(){
        //since ee1.12+
        $minPrice = $this->getLayer()->getProductCollection()->getMinPrice();
        return floor($minPrice);
    }

    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        /**
         * Filter must be string: $fromPrice-$toPrice
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

        //validate filter
        $filterParams = explode(',', $filter);
        $filter = $this->_validateFilter($filterParams[0]);
        if (!$filter) {
            return $this;
        }

        list($from, $to) = $filter;

        $this->setInterval(array($from, $to));

        $priorFilters = array();
        for ($i = 1; $i < count($filterParams); ++$i) {
            $priorFilter = $this->_validateFilter($filterParams[$i]);
            if ($priorFilter) {
                $priorFilters[] = $priorFilter;
            } else {
                //not valid data
                $priorFilters = array();
                break;
            }
        }
        if ($priorFilters) {
            $this->setPriorIntervals($priorFilters);
        }

        $this->_applyPriceRange();
        if(empty($from)) $this->setData('current_min',0);
        else $this->setData('current_min',$from);
        if(empty($to)) $this->setData('current_max',0);
        else $this->setData('current_max',$to);
        $this->getLayer()->getState()->addFilter($this->_createItem(
            $this->_renderRangeLabel(empty($from) ? 0 : $from, $to),
            $filter
        ));

        return $this;
    }
    
    protected function _renderRangeLabel($fromPrice, $toPrice)
    {
        $store      = Mage::app()->getStore();
        $formattedFromPrice  = strip_tags($store->formatPrice($fromPrice));
        if ($toPrice === '') {
            return Mage::helper('catalog')->__('%s and above', $formattedFromPrice);
        } elseif ($fromPrice == $toPrice && Mage::app()->getStore()->getConfig(self::XML_PATH_ONE_PRICE_INTERVAL)) {
            return $formattedFromPrice;
        } else {
            if ($fromPrice != $toPrice) {
                $toPrice -= .01;
            }
            return Mage::helper('catalog')->__('%s to %s', $formattedFromPrice, strip_tags($store->formatPrice($toPrice)));
        }
    }
    
    //always show
    public function getItemsCount(){
        $parent_itemcount = parent::getItemsCount();
        //if selected before then shows
        $params = Mage::app()->getRequest()->getParams();
        $keys = array_keys($params);
        $name = $this->getName();
        $clean_name = $this->_helper->labelEncode($name);
        if(in_array($this->getRequestVar(),$keys) || in_array($clean_name,$keys)) return 1;
        elseif($parent_itemcount>1){ //if only one option no need to show
            return parent::getItemsCount();
        }
    }
}
