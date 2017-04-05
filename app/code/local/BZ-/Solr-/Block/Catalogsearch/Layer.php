<?php
class BZ_Solr_Block_Catalogsearch_Layer extends Mage_CatalogSearch_Block_Layer
{
    /**
     * Initialize blocks names
     */
    protected function _initBlocks()
    {
        parent::_initBlocks();

        $helper = Mage::helper('bz_solr');
        if ($helper->isThirdPartSearchEngine() && Mage::helper('bz_solr')->getIsEngineAvailableForNavigation()) {
            $this->_stateBlockName           = 'bz_navigation/catalog_layer_state';
            $this->_categoryBlockName        = 'bz_solr/catalog_layer_filter_category';
            $this->_attributeFilterBlockName = 'bz_solr/catalogsearch_layer_filter_attribute';
            $this->_priceFilterBlockName     = 'bz_solr/catalog_layer_filter_price';
            $this->_decimalFilterBlockName   = 'bz_solr/catalog_layer_filter_decimal';
        } elseif($helper->isModuleEnabled('BZ_Navigation')){
            $this->_stateBlockName           = 'bz_navigation/catalog_layer_state';
            $this->_categoryBlockName        = 'bz_navigation/catalog_layer_filter_category';
            $this->_attributeFilterBlockName = 'bz_navigation/catalogsearch_layer_filter_attribute';
            $this->_priceFilterBlockName     = 'bz_navigation/catalog_layer_filter_price';
            $this->_decimalFilterBlockName   = 'bz_navigation/catalog_layer_filter_decimal';
        }
    }

    protected function _prepareLayout()
    {
        $helper = Mage::helper('bz_solr');
        if ($helper->isThirdPartSearchEngine() && $helper->getIsEngineAvailableForNavigation(false)) {
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
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowBlock()
    {
        $helper = Mage::helper('bz_solr');
        if ($helper->isThirdPartSearchEngine() && $helper->isActiveEngine()) {
            return ($this->canShowOptions() || count($this->getLayer()->getState()->getFilters()));
        }
        return parent::canShowBlock();
    }

    /**
     * Get layer object
     *
     * @return Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        $helper = Mage::helper('bz_solr');
        if ($helper->isThirdPartSearchEngine() && $helper->isActiveEngine()) {
            return Mage::getSingleton('bz_solr/search_layer');
        }

        return parent::getLayer();
    }
}
