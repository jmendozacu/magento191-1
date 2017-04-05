<?php
/**
 * Class Attribute
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Model_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute
{
    /**
     * Apply attribute option filter to product collection
     * support multiple options
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Varien_Object $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Attribute
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        //check use friendly url or not
        $helper = Mage::helper('bz_navigation');
        if(!$helper) return parent::apply($request,$filterBlock);
        
        $attribute_label = $this->getAttributeModel()->getFrontendLabel();
        
        //Start comment on 2014-apr-09 By GN
        //This part of the code commented by me to fix SKIN-903
        if($attribute_label){
            $attribute_label_key = $helper->labelEncode($attribute_label);
        }
        else{
            $attribute_label_key = $this->getRequestVar();
        }
        //End comment on 2014-apr-09 By GN

		//Start Add to solve SKIN-903 
		//This solution taken from TAF
		
        if($attribute_label){
            $attribute_label_key = $helper->labelEncode($attribute_label);
            //Fix to taf this is due to extended attributes
            //Fixed added by Amal Shashika
            //store front label usage fix
            if(!$request->getParam($attribute_label_key)){
                $attribute_label_key = $helper->labelEncode($this->getAttributeModel()->getStoreLabel());
            }
        }
        else{
            $attribute_label_key = $this->getRequestVar();
        }
		//End Add to solve SKIN-903
        
        //$this->_requestVar
        $filter = $request->getParam($attribute_label_key);
        if (is_array($filter)) {
            return $this;
        }
        //Multiple facet separator
        $separator = $helper->getFacetSeparator();
        if($separator) $filters = explode($separator, $filter);
        else $filters = array($filter);
        $filter_names = array();
        $apply_filters = array();
        foreach($filters as $f){
            //make sure the filer exists
            if($f){
                $arr = $this->_getOptionId($f);
                if($arr && is_array($arr)){
                    $filter_names[] = $arr[1];
                    $apply_filters[] = $arr[0];
                }
            }
        }
        if(!empty($apply_filters)){
            $this->_getResource()->applyFilterToCollection($this, $apply_filters);
            $this->getLayer()->getState()->addFilter($this->_createItemWithContent($filter_names, $filter, $apply_filters, 0));
            //no follow for search engine
            //if(count($apply_filters>0)) $this->setData('nofollow', 1);
            //else $this->setData('nofollow', 0);
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
    
    protected function _getItemsData()
    {
        $data = parent::_getItemsData();
        $option_ids = array();
        foreach($data as $item){
            if(isset($item['value'])) $option_ids[] = $item['value'];
        }
        $collection = Mage::getModel('bz_navigation/filter_option')->getCollection();
        $filter_options = $collection->addFieldToFilter('option_id',array('in'=>$option_ids))->addFieldToFilter('store_id',0);
        foreach($data as &$item){
            if(isset($item['value'])){
                foreach ($filter_options as $op) {
                    if ($op->getOptionId() == $item['value']){
                        $item['file1'] = $op->getFilename();
                        $item['file2'] = $op->getFilenameOne();
                        $item['file3'] = $op->getFilenameTwo();
                        $item['color'] = $op->getColorCode();
                    }
                }
            }
        }
        return $data;
    }
    
    protected function _initItems()
    {
        $data = $this->_getItemsData();
        $items=array();
        foreach ($data as $itemData) {
            $items[] = $this->_createItemData($itemData);
        }
        $this->_items = $items;
        return $this;
    }
    
    //custom function to add additional details to items
    protected function _createItemData($data)
    {
        $item = Mage::getModel('catalog/layer_filter_item')->setFilter($this);
        foreach($data as $k => $v){
            $item->setData($k,$v);
        }
        return $item;
    }

    protected function _getOptionId($text)
    {
        //check use friendly url or not
        $helper = Mage::helper('bz_navigation');
        if(!$helper) return parent::_getOptionId($text);
        
        $options = $this->getAttributeModel()->getSource()->getAllOptions();
        if($options){
            foreach ($options as $option) {
                if ($option['label']) {
                    $label = $helper->labelEncode($option['label']);
                    if ($text == $label) {
                        return array($option['value'],$option['label']);
                    }
                }
            }
        }
        return false;
    }
}
