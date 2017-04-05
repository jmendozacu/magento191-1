<?php
/**
 * Class Attribute
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Block_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Block_Layer_Filter_Attribute
{
    protected $_filter_config;
    
    public function __construct(){
        parent::__construct();
        $this->_filterModelName = 'bz_navigation/catalog_layer_filter_attribute';
        $this->setTemplate('bz_navigation/filters/attribute.phtml');
        $helper = Mage::helper('bz_navigation');
        $settings = $helper->loadFilterSettings();
        if($settings && !empty($settings)) $this->_filter_config = $settings;
    }
    
    public function getFilterConfig(){
        return $this->_filter_config;
    }
}
