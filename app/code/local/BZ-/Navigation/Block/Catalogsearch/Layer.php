<?php
/**
 * Class Layer
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Block_Catalogsearch_Layer extends Mage_CatalogSearch_Block_Layer
{
    protected function _initBlocks()
    {
        //parent::_initBlocks(); no need as we defined all here
        $this->_stateBlockName = 'bz_navigation/catalog_layer_state';
        $this->_categoryBlockName = 'bz_navigation/catalog_layer_filter_category';
        $this->_attributeFilterBlockName = 'bz_navigation/catalogsearch_layer_filter_attribute';
        $this->_priceFilterBlockName = 'bz_navigation/catalog_layer_filter_price';
        $this->_decimalFilterBlockName = 'bz_navigation/catalog_layer_filter_decimal';
    }
     
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $state = $this->getChild('layer_state');
        $filters = $state->getActiveFilters();
        $title = $this->getLayout()->getBlock('head')->getTitle();
        $names = array();
        foreach($filters as $f){
            $names[] = $f->getName().' '.$f->getLabel();
        }
        if (!empty($names)) {
            $title .= ' with ' . implode(' and ', $names);
            $this->getLayout()->getBlock('head')->setTitle($title);
        }
        return $this;
    }
}
