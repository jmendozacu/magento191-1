<?php
class BZ_Solr_Block_Catalogsearch_Layer_Filter_Attribute extends Mage_Catalog_Block_Layer_Filter_Abstract
{
    protected $_filter_config;
    /**
     * Set model name
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'bz_solr/search_layer_filter_attribute';
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

    /**
     * Set attribute model
     */
    protected function _prepareFilter()
    {
        $this->_filter->setAttributeModel($this->getAttributeModel());
        return $this;
    }

    /**
     * Add params to faceted search
     */
    public function addFacetCondition()
    {
        $this->_filter->addFacetCondition();
        return $this;
    }
}
