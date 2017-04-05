<?php

class BZ_Solr_Model_Catalog_Layer_Filter_Decimal extends Mage_Catalog_Model_Layer_Filter_Decimal
{
    /**
     * Get data for build decimal filter items
     * @return array
     */
    protected function _getItemsData()
    {
        $range = $this->getRange();
        $attribute_code = $this->getAttributeModel()->getAttributeCode();
        $facets = $this->getLayer()->getProductCollection()->getFacetedData('attr_decimal_' . $attribute_code);

        $data = array();
        if (!empty($facets)) {
            foreach ($facets as $key => $count) {
                preg_match('/TO ([\d\.]+)\]$/', $key, $rangeKey);
                $rangeKey = $rangeKey[1] / $range;
                if ($count > 0) {
                    $rangeKey = round($rangeKey);
                    $data[] = array(
                        'label' => $this->_renderItemLabel($range, $rangeKey),
                        'value' => $rangeKey . ',' . $range,
                        'count' => $count,
                    );
                }
            }
        }
        return $data;
    }
    
    /**
     * Apply decimal range filter to product collection
     * @param Zend_Controller_Request_Abstract $request
     * @param Mage_Catalog_Block_Layer_Filter_Decimal $filterBlock
     * @return Mage_Catalog_Model_Layer_Filter_Decimal
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        /**
         * Filter must be string: $from, $to
         */
        $filter = $request->getParam($this->getRequestVar());
        $helper = Mage::helper('bz_solr/navigation');
        if(!$filter){
            $name = $filterBlock->getName();
            $clean_name = $helper->labelEncode($name);
            $filter = $request->getParam($clean_name);
        }
        if (!$filter) {
            return $this;
        }

        $filter = $this->_validateFilter($filter);
        if (count($filter) != 2 || $filter===false) {
            return $this;
        }
        
        list($from, $to) = $filter;
        if(empty($from)) $this->setData('current_min',0);
        else $this->setData('current_min',$from);
        if(empty($to)) $this->setData('current_max',0);
        else $this->setData('current_max',$to);
        
        if ((int)$from && (int)$to) {
            $this->applyFilterToCollection($this, $from, $to);
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

    /**
     * Add params to faceted search
     * @return this
     */
    public function addFacetCondition()
    {
        $range    = $this->getRange();
        $maxValue = $this->getMaxValue();
        if ($maxValue > 0) {
            $facets = array();
            $facetCount = ceil($maxValue / $range);
            for ($i = 0; $i < $facetCount; $i++) {
                $facets[] = array(
                    'from' => $i * $range,
                    'to'   => ($i + 1) * $range - 0.001
                );
            }
            $attributeCode = $this->getAttributeModel()->getAttributeCode();
            $field         = 'attr_decimal_' . $attributeCode;
            $this->getLayer()->getProductCollection()->setFacetCondition($field, $facets);
        }
        return $this;
    }

    /**
     * Apply attribute filter to product collection
     */
    public function applyFilterToCollection($filter, $from, $to)
    {
        $productCollection = $filter->getLayer()->getProductCollection();
        $attributeCode     = $filter->getAttributeModel()->getAttributeCode();
        $field             = 'attr_decimal_'. $attributeCode;

        $value = array(
            $field => array(
                'from' => $from,
                'to'   => $to - 0.001
            )
        );

        $productCollection->addFqFilter($value);
        return $this;
    }
    
    /**
     * working for bz_navigation store the old price and curretn filtered price for price slider
     */
    public function getCurrentMaxPrice(){
        if(!is_null($this->getData('current_max'))) return $this->getData('current_max');
        else return 0;
    }
    
    public function getCurrentMinPrice(){
        if(!is_null($this->getData('current_min'))) return $this->getData('current_min');
        else return 0;
    }
    
    protected function _loadOriginalMaxMinPrice(){
        $prices = $this->getLayer()->getProductCollection()->getOriginalPriceStats($this->_getFilterField());
        if(isset($prices[$this->_getFilterField()]['max'])) $this->setData('org_max', ceil($prices[$this->_getFilterField()]['max']+0.01));
        if(isset($prices[$this->_getFilterField()]['min'])) $this->setData('org_min', floor($prices[$this->_getFilterField()]['min']));
    }

    public function getOriginalMaxPirce(){
        if(!is_null($this->getData('org_max'))) return $this->getData('org_max');
        else {
            $this->_loadOriginalMaxMinPrice();
            return $this->getData('org_max');
        }
    }
    
    public function getOriginalMinPirce(){
        if(!is_null($this->getData('org_min'))) return $this->getData('org_min');
        else {
            $this->_loadOriginalMaxMinPrice();
            return $this->getData('org_min');
        }
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
    
    //always shows
    public function getItemsCount(){
        //if selected before then shows
        $params = Mage::app()->getRequest()->getParams();
        $keys = array_keys($params);
        $name = $this->getName();
        $helper = Mage::helper('bz_solr/navigation');
        $clean_name = $helper->labelEncode($name);
        if(in_array($this->getRequestVar(),$keys) || in_array($clean_name,$keys)) return 1;
        else return parent::getItemsCount();
    }
    
    protected function _getFilterField()
    {
        $attribute_code = $this->getAttributeModel()->getAttributeCode();
        $priceField = 'attr_decimal_' . $attribute_code;
        return $priceField;
    }
    
}
