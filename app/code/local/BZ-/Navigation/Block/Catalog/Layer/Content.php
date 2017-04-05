<?php
/**
 * Class Content
 *
 * @author Ben Zhang <bzhang@netstarter.com>
 */
class BZ_Navigation_Block_Catalog_Layer_Content extends Mage_Catalog_Block_Layer_State
{
    protected $_processor;
    
    public function _construct()
    {
        $helper = Mage::helper('cms');
        $this->_processor = $helper->getBlockTemplateProcessor();
        $this->setTemplate('bz_navigation/content.phtml');
    }
    
    public function getProcessor(){
        return $this->_processor;
    }
    
    public function getLayer()
    {
        //load the layer from registry as solr use observer to update the layer.
        $current_layer = Mage::registry('current_layer');
        if($current_layer){
            $this->setLayer($current_layer);
        }elseif (!$this->hasData('layer')) {
            $this->setLayer(Mage::getSingleton('catalog/layer'));
        }
        return $this->_getData('layer');
    }
    
}
