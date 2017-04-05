<?php
/**
 * Class Filter
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Model_Filter extends Mage_Core_Model_Abstract
{
    const BZ_NAVIGATION_FILTER_USE_IMAGE = 1;
    const BZ_NAVIGATION_FILTER_USE_IMAGE_LABEL = 2;
    const BZ_NAVIGATION_FILTER_USE_COLOR = 3;
    const BZ_NAVIGATION_FILTER_USE_COLOR_LABEL = 4;
    
    public function _construct() {
        parent::_construct();
        $this->_init('bz_navigation/filter');
    }
    
    public function loadByAttributeId($attr_id){
        $model = $this->load($attr_id,'attribute_id');
        return $model;
    }
}
