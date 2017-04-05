<?php
/**
 * Class Filter
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Block_Catalog_Layer_Filter_Category extends Mage_Catalog_Block_Layer_Filter_Category
{
    public function __construct(){
        parent::__construct();
        $this->_filterModelName = 'bz_navigation/catalog_layer_filter_category';
        $this->setTemplate('bz_navigation/filters/category.phtml');
    }

    public function getCurrentFilter()
    {
        return $this->_filter->getFilterPath();
    }
}
