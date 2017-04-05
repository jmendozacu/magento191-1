<?php

/**
 * Open source project for Magento EE and CE version.
 * License 
 */

/**
 * Class Engine
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Solr_Helper_Engine extends Mage_CatalogSearch_Helper_Data
{
    public function getEngine()
    {
        if (!$this->_engine) {
            $engine = Mage::getStoreConfig('catalog/solr_search/engine');
            if($engine == 'no'){
                $engine = Mage::getStoreConfig('catalog/search/engine');
            }
            /**
             * This needed if there already was saved in configuration some none-default engine
             * and module of that engine was disabled after that.
             * Problem is in this engine in database configuration still set.
             */
            if ($engine && Mage::getConfig()->getResourceModelClassName($engine)) {
                $model = Mage::getResourceSingleton($engine);
                if ($model && $model->test()) {
                    $this->_engine = $model;
                }
            }
            if (!$this->_engine) {
                $this->_engine = Mage::getResourceSingleton('catalogsearch/fulltext_engine');
            }
        }

        return $this->_engine;
    }        
}
