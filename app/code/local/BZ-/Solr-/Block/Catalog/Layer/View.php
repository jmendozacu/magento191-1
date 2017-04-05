<?php

class BZ_Solr_Block_Catalog_Layer_View extends Mage_Catalog_Block_Layer_View
{
    /**
     * Initialize blocks names
     */
    protected function _initBlocks()
    {
        parent::_initBlocks();
        $helper = Mage::helper('bz_solr');
        if ($helper->isThirdPartSearchEngine() && Mage::helper('bz_solr')->getIsEngineAvailableForNavigation()) {
            if($helper->isModuleEnabled('BZ_Navigation')) $this->_stateBlockName = 'bz_navigation/catalog_layer_state';
            $this->_categoryBlockName        = 'bz_solr/catalog_layer_filter_category';
            $this->_attributeFilterBlockName = 'bz_solr/catalog_layer_filter_attribute';
            $this->_priceFilterBlockName     = 'bz_solr/catalog_layer_filter_price';
            $this->_decimalFilterBlockName   = 'bz_solr/catalog_layer_filter_decimal';
        } elseif($helper->isModuleEnabled('BZ_Navigation')){
            $this->_stateBlockName           = 'bz_navigation/catalog_layer_state';
            $this->_categoryBlockName        = 'bz_navigation/catalog_layer_filter_category';
            $this->_attributeFilterBlockName = 'bz_navigation/catalog_layer_filter_attribute';
            $this->_priceFilterBlockName     = 'bz_navigation/catalog_layer_filter_price';
            $this->_decimalFilterBlockName   = 'bz_navigation/catalog_layer_filter_decimal';
        }
    }

    protected function _prepareLayout()
    {
        $helper = Mage::helper('bz_solr');
        if ($helper->isThirdPartSearchEngine() && $helper->getIsEngineAvailableForNavigation()) {
            $stateBlock = $this->getLayout()->createBlock($this->_stateBlockName)
                ->setLayer($this->getLayer());

            $categoryBlock = $this->getLayout()->createBlock($this->_categoryBlockName)
                ->setLayer($this->getLayer())
                ->init();

            $filterableAttributes = $this->_getFilterableAttributes();
            $filters = array();
            foreach ($filterableAttributes as $attribute) {
                if ($attribute->getAttributeCode() == 'price') {
                    $filterBlockName = $this->_priceFilterBlockName;
                } elseif ($attribute->getBackendType() == 'decimal') {
                    $filterBlockName = $this->_decimalFilterBlockName;
                } else {
                    $filterBlockName = $this->_attributeFilterBlockName;
                }

                $filters[$attribute->getAttributeCode() . '_filter'] = $this->getLayout()->createBlock($filterBlockName)
                    ->setLayer($this->getLayer())
                    ->setAttributeModel($attribute)
                    ->init();
            }

            $this->setChild('layer_state', $stateBlock);
            $this->setChild('category_filter', $categoryBlock->addFacetCondition());

            foreach ($filters as $filterName => $block) {
                $this->setChild($filterName, $block->addFacetCondition());
            }

            $this->getLayer()->apply();
        } else {
            parent::_prepareLayout();
        }
        //if bz navigation module there
        $navigation_helper = Mage::helper('bz_navigation');
        if($navigation_helper){
            $settings = $navigation_helper->loadFilterSettings();
            if(is_array($settings) && !empty($settings)) $this->setFilterConfig($settings);
            //SEO update page title or meta data
            $navigation_helper->updateMeta($this, $settings);
        }
        return $this;
    }

    /**
     * Get layer object
     *
     * @return Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        if (Mage::helper('bz_solr')->getIsEngineAvailableForNavigation()) {
            return Mage::getSingleton('bz_solr/catalog_layer');
        }

        return parent::getLayer();
    }
    
    /**
     * Get all layer filters and adding configuration, instead of query each filter to db
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array();
        if ($categoryFilter = $this->_getCategoryFilter()) {
            $filters[] = $categoryFilter->setAttrId(0);
        }

        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {
            $filters[] = $this->getChild($attribute->getAttributeCode() . '_filter')->setAttrId($attribute->getId());
        }

        return $filters;
    }
}
