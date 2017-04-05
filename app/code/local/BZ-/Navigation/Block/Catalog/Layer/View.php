<?php
/**
 * Class View
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Block_Catalog_Layer_View extends Mage_Catalog_Block_Layer_View
{
    /**
     * update and change the block without rewrite catalog block
     */
    protected function _initBlocks() {
        $this->_stateBlockName = 'bz_navigation/catalog_layer_state';
        $this->_categoryBlockName = 'bz_navigation/catalog_layer_filter_category';
        $this->_attributeFilterBlockName = 'bz_navigation/catalog_layer_filter_attribute';
        $this->_priceFilterBlockName = 'bz_navigation/catalog_layer_filter_price';
        $this->_decimalFilterBlockName = 'bz_navigation/catalog_layer_filter_decimal';
    }
    
    //adding staff to page title for SEO
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $helper = Mage::helper('bz_navigation');
        $settings = $helper->loadFilterSettings();
        if($settings && !empty($settings)) $this->setFilterConfig($settings);
        $helper->updateMeta($this, $settings);
        return $this;
    }
    
    /**
     * Get all layer filters and adding configuration, instead of query each filter to db
     * set ids to the blocks classes
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