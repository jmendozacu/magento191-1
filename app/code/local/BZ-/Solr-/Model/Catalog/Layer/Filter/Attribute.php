<?php
/**
 * attribute model for solr
 */
class BZ_Solr_Model_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute
{
    /**
     * Get data array for building attribute filter items
     * @return array
     */
    protected function _getItemsData()
    {
        $helper = Mage::helper('bz_solr/navigation');
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();
        $attribute_label = $this->getAttributeModel()->getFrontendLabel();
        $attribute_label_key = $helper->labelEncode($attribute_label);

        $engine = Mage::getResourceSingleton('bz_solr/engine');
        $fieldName = $engine->getSearchEngineFieldName($attribute, 'nav');

        $productCollection = $this->getLayer()->getProductCollection();
        $current_filter = Mage::app()->getRequest()->getParam($attribute_label_key,false);
        //if it is current selected filter then clean itself
        if($current_filter)
            $optionsFacetedData = $productCollection->getFacetedDataWithoutSelf($fieldName);
        else
            $optionsFacetedData = $productCollection->getFacetedData($fieldName);
        $options = $attribute->getSource()->getAllOptions(false);
        $option_ids = array();
        $data = array();
        foreach ($options as $option) {
            $optionId = $option['value'];
            $option_ids[] = $option['value'];
            // Check filter type
            if ($this->_getIsFilterableAttribute($attribute) != self::OPTIONS_ONLY_WITH_RESULTS
                || !empty($optionsFacetedData[$optionId])
            ) {
                $data[] = array(
                    'label' => $option['label'],
                    'value' => $helper->labelEncode($option['label']),
                    'count' => isset($optionsFacetedData[$optionId]) ? $optionsFacetedData[$optionId] : 0,
                    'value_id' => $option['value']
                );
            }
        }

        //adding additional details for items when bz_navigation is on
        if ($helper->hasNavigationModule()) {
            $collection = Mage::getModel('bz_navigation/filter_option')->getCollection();
            $filter_options = $collection->addFieldToFilter('option_id', array('in' => $option_ids))->addFieldToFilter('store_id', 0);
            foreach ($data as &$item) {
                if (isset($item['value_id'])) {
                    foreach ($filter_options as $op) {
                        if ($op->getOptionId() == $item['value_id']) {
                            $item['file1'] = $op->getFilename();
                            $item['file2'] = $op->getFilenameOne();
                            $item['file3'] = $op->getFilenameTwo();
                            $item['color'] = $op->getColorCode();
                            break;
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Apply attribute filter to layer
     * @param Zend_Controller_Request_Abstract $request
     * @param object $filterBlock
     * @return this
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $helper = Mage::helper('bz_solr/navigation');
        $attribute_label = $this->getAttributeModel()->getFrontendLabel();
        $attribute_label_key = $helper->labelEncode($attribute_label);
        $filter = $request->getParam($attribute_label_key, false);
        if($filter === false) $filter = $request->getParam($this->_requestVar, false);
        if (is_array($filter)) {
            return $this;
        }
        //Multiple facet separator
        $separator = $helper->getFacetSeparator();
        if ($separator)
            $filters = explode($separator, $filter);
        else
            $filters = array($filter);
        $filter_names = array();
        $apply_filters = array();
        $options = $this->getAttributeModel()->getSource()->getAllOptions(false);
        $option_text_value = array();
        foreach($options as $op){
            $label = $helper->labelEncode($op['label']);
            $option_text_value[$label] = array($op['value'],$op['label']);
        }
        foreach ($filters as $f) {
            //make sure the filer exists
            if ($f) {
                if (in_array($f,array_keys($option_text_value))) {
                    $filter_names[] = $option_text_value[$f][1];
                    $apply_filters[] = $option_text_value[$f][0];
                }
            }
        }
        if (!empty($apply_filters)) {
            $this->applyFilterToCollection($this, $apply_filters);
            //$this->getLayer()->getState()->addFilter($this->_createItem(implode(',', $filter_names), $filter));
            $this->getLayer()->getState()->addFilter($this->_createItemWithContent($filter_names,$filter,$apply_filters,0));
        }
        return $this;
        
    }
    
    /**
     * create select filter items with SEO
     * array $labels
     * 
     * @return 
     */
    protected function _createItemWithContent($labels, $value, $option_ids, $count = 0)
    {
        $label = implode(',', $labels);
        if(is_array($option_ids) && !empty($option_ids)){
            $collection = Mage::getModel('bz_navigation/filter_option')->getCollection();
            $collection->addFieldToSelect('block_id')
                    ->addFieldToFilter('option_id',array('in'=>$option_ids))
                    ->addFieldToFilter('store_id',0)
                    ->addFieldToFilter('block_id',array('notnull' => true));
            $block_ids =array();
            foreach($collection as $result){
                if($result->getBlockId()) $block_ids[] = $result->getBlockId();
            }
        }
        return Mage::getModel('catalog/layer_filter_item')
            ->setFilter($this)
            ->setOptionIds($option_ids)
            ->setLabel($label)
            ->setValue($value)
            ->setCount($count)
            ->setBlockIds($block_ids);
    }

    /**
     * Add params to faceted search
     * @return this
     */
    public function addFacetCondition()
    {
        $engine = Mage::getResourceSingleton('bz_solr/engine');
        $facetField = $engine->getSearchEngineFieldName($this->getAttributeModel(), 'nav');
        $this->getLayer()->getProductCollection()->setFacetCondition($facetField);
        return $this;
    }

    /**
     * Apply attribute filter to solr query
     * @param   Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @param   $value int | array()
     * @return  this
     */
    public function applyFilterToCollection($filter, $value)
    {
        if (empty($value) || (isset($value['from']) && empty($value['from']) && isset($value['to'])
            && empty($value['to']))
        ) {
            $value = array();
        }
        if (!is_array($value)) {
            $value = array($value);
        }
        $attribute = $filter->getAttributeModel();
        /*$options = $attribute->getSource()->getAllOptions();
        foreach ($value as &$valueText) {
            foreach ($options as $option) {
                if ($option['label'] == $valueText) {
                    $valueText = $option['value'];
                }
            }
        }*/
        $fieldName = Mage::getResourceSingleton('bz_solr/engine')
            ->getSearchEngineFieldName($attribute, 'nav');
        $this->getLayer()->getProductCollection()->addFqFilter(array($fieldName => $value));
        return $this;
    }
    
    /**
     * The abstract class only has 3 field for each item such as label,value,count
     */
    protected function _initItems(){
        $data = $this->_getItemsData();
        $items=array();
        foreach ($data as $itemData) {
            $items[] = $this->_createItemData($itemData);
        }
        $this->_items = $items;
        return $this;
    }
    
    protected function _createItemData($data){
        $item = Mage::getModel('catalog/layer_filter_item')->setFilter($this);
        foreach($data as $k => $v){
            $item->setData($k,$v);
        }
        return $item;
    }
    
}
