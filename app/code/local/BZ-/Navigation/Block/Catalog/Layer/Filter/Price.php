<?php
/**
 * Class Price
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Block_Catalog_Layer_Filter_Price extends Mage_Catalog_Block_Layer_Filter_Price
{
    public function __construct(){
        parent::__construct();
        $this->_filterModelName = 'bz_navigation/catalog_layer_filter_price';
        $this->setTemplate('bz_navigation/filters/price.phtml');
    }
}
