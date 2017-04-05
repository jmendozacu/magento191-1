<?php

class Netstarter_Extcatalog_Block_Layer_View extends Mage_Catalog_Block_Layer_View
{
       /**
     * Initialize blocks names
     */
    protected function _initBlocks()
    {
        $this->_categoryBlockName           = 'extcatalog/layer_filter_category';
    }
    /**
     * Prepare child blocks
     *
     * @return Mage_Catalog_Block_Layer_View
     */
    protected function _prepareLayout()
    {
        $categoryBlock = $this->getLayout()->createBlock($this->_categoryBlockName)
            ->setLayer($this->getLayer())
            ->init();

        $this->setChild('category_filter', $categoryBlock);

        $this->getLayer()->apply();

        return Mage_Core_Block_Template::_prepareLayout();
    }

    public function getFilters()
    {
        $filters = array();
        if ($categoryFilter = $this->_getCategoryFilter()) {
            $filters['category'] = $categoryFilter;
        }

        return $filters;
    }

    public function canShowOptions()
    {
        foreach ($this->getFilters() as $filter) {

            if(is_array($filter)){

                foreach ($filter as $item) {

                    if ($item->getItemsCount()) {
                        return true;
                    }
                }

            }else{

                if ($filter->getItemsCount()) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getBaseClearUrl()
    {
        return Mage::registry('current_category')->getUrl();
    }
}
