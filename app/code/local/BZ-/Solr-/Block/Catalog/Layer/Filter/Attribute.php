<?php

class BZ_Solr_Block_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Block_Layer_Filter_Abstract
{
    protected $_filter_config;
    /**
     * Set model name
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'bz_solr/catalog_layer_filter_attribute';
        //check bz_navigation before using its template
        if(Mage::helper('bz_solr/navigation')->hasNavigationModule()){
            $this->setTemplate('bz_navigation/filters/attribute.phtml');
            $helper = Mage::helper('bz_navigation');
            $settings = $helper->loadFilterSettings();
            if ($settings && !empty($settings)){
                $this->_filter_config = $settings;
            }
        }
    }
    
    public function getFilterConfig(){
        return $this->_filter_config;
    }

    protected function _prepareFilter()
    {
        $this->_filter->setAttributeModel($this->getAttributeModel());
        return $this;
    }

    public function addFacetCondition()
    {
        $this->_filter->addFacetCondition();
        return $this;
    }

    public function getItemsCount()
    {
        $attributeIsFilterable = $this->getAttributeModel()->getIsFilterable();
        if ($attributeIsFilterable == Mage_Catalog_Model_Layer_Filter_Attribute::OPTIONS_ONLY_WITH_RESULTS) {
            return parent::getItemsCount();
        }

        $count = 0;
        foreach ($this->getItems() as $item) {
            if ($item->getCount()) {
                $count++;
            }
        }

        return $count;
    }
}
