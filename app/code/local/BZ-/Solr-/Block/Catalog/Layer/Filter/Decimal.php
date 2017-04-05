<?php

class BZ_Solr_Block_Catalog_Layer_Filter_Decimal extends Mage_Catalog_Block_Layer_Filter_Abstract
{
    /**
     * Initialize Decimal Filter Model
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'bz_solr/catalog_layer_filter_decimal';
        if(Mage::helper('bz_solr/navigation')->hasNavigationModule()){
            $this->setTemplate('bz_navigation/filters/price.phtml');
        }
    }

    /**
     * Prepare filter process
     *
     * @return Mage_Catalog_Block_Layer_Filter_Decimal
     */
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
