<?php

class BZ_Solr_Block_Catalog_Layer_Filter_Price extends Mage_Catalog_Block_Layer_Filter_Abstract
{
    /**
     * Initialize Price filter module
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'bz_solr/catalog_layer_filter_price';
        if(Mage::helper('bz_solr/navigation')->hasNavigationModule()){
            $this->setTemplate('bz_navigation/filters/price.phtml');
        }
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
}
