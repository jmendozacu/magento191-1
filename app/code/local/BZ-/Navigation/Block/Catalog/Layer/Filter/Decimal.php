<?php
/**
 * Class Decimal
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Block_Catalog_Layer_Filter_Decimal extends Mage_Catalog_Block_Layer_Filter_Decimal
{
    public function __construct(){
        parent::__construct();
        $this->_filterModelName = 'bz_navigation/catalog_layer_filter_decimal';
        $this->setTemplate('bz_navigation/filters/price.phtml');
    }
}
