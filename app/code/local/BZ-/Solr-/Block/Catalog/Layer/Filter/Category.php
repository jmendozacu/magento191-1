<?php

class BZ_Solr_Block_Catalog_Layer_Filter_Category extends Mage_Catalog_Block_Layer_Filter_Abstract
{
    /**
     * Set model name
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'bz_solr/catalog_layer_filter_category';
        if(Mage::helper('bz_solr/navigation')->hasNavigationModule()){
            $this->setTemplate('bz_navigation/filters/category_search.phtml');
        }
    }

    public function addFacetCondition()
    {
        $this->_filter->addFacetCondition();
        return $this;
    }
}
