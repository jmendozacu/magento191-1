<?php
/**
 * Class Index Resource Cms
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Solr_Model_Resource_Indexer_Cms extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct() {
        $this->_engine = Mage::getResourceSingleton('bz_solr/engine');
    }
    
    public function rollBackChanges(){
        $this->_engine->rollBackChanges();
    }
    
    public function rebuildIndex($storeId = null, $entityIds = null, $entityType = 'cms')
    {
        //do observer staff before index
        if (!$this->_engine->holdCommit()) {
            return;
        }
        //Index needs to be optimized if all entities of this type were affected
        if (is_null($entityIds)) {
            $this->_engine->setIndexNeedsOptimization();
        }
        if (is_null($storeId)) {
            $storeIds = array_keys(Mage::app()->getStores());
            foreach ($storeIds as $storeId) {
                $this->_rebuildStoreIndex($storeId, $entityIds, $entityType);
            }
        } else {
            $this->_rebuildStoreIndex($storeId, $entityIds, $entityType);
        }
        
        //do observer staff after index
        if (!$this->_engine->allowCommit()) {
            return;
        }
        if ($this->_engine->getIndexNeedsOptimization()) {
            $this->_engine->optimizeIndex();
        } else {
            $this->_engine->commitChanges();
        }
        return $this;
    }
    
    protected function _rebuildStoreIndex($storeId, $entityIds, $entityType = 'cms')
    {
        //must provide $entityType or product will be clean
        $this->_engine->cleanIndex($storeId, $entityIds, $entityType);
        $p = 1;
        //temporary setting frontend and store id in design
        Mage::getDesign()->setStore($storeId)->setArea('frontend');
        $helper = Mage::helper('cms');
        $processor = $helper->getBlockTemplateProcessor();
        //define field index or need to be skip html coding
        $index_fields = array('title', 'meta_keywords', 'meta_description', 'content_heading','identifier');
        $trim_fields = array('content');
        while (true) {
            //load pages by default limit 100
            $cms_pages = $this->_getSearchableEntityByLimit($storeId, $entityIds, $p);
            if (!$cms_pages) {
                break;
            }
            $p++;
            //process and saving the first limit (100) data to solr
            foreach ($cms_pages as $page) {
                $data = $page->getData();
                $index = array();
                foreach ($data as $k => $v) {
                    if (in_array($k, $trim_fields)) {
                        if ($k == 'content')
                            $v = $processor->filter($v);
                        $output = $this->_engine->strip_html($v);
                        $index[$k] = $output;
                    }
                    elseif (in_array($k, $index_fields)) {
                        $index[$k] = $v;
                    }
                }
                $solr_index = $this->prepareIndexData($index, $page->getId(), $storeId, $entityType);
                $cmsIndexes[$page->getId()] = $solr_index;
            }
            //save data to solr by limits
            $this->_engine->saveEntityIndexes($storeId, $cmsIndexes, 'cms');
        }
        return $this;
    }
    
    //as post to solr may have size limit so not too many docs send to solr at one http request
    protected function _getSearchableEntityByLimit($storeId, $entityIds, $p, $limit = 100)
    {
        $cms_pages = Mage::getSingleton('cms/page')->getCollection()
                ->addStoreFilter($storeId, true)
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('is_searchable', 1);
        $cms_pages->getSelect()->limitPage($p, $limit);
        echo $cms_pages->getSelect();
        if(!is_null($entityIds) && is_array($entityIds) && !empty($entityIds))
            $cms_pages->addFieldToFilter('page_id',array('in'=>$entityIds));
        if(count($cms_pages) > 0)
            return $cms_pages;
        else
            return false; 
    }

    public function prepareIndexData($entityIndexData, $entityId, $storeId, $entityType) {
        $fulltextData = array();
        $localeCode = Mage::app()->getStore($storeId)->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
        $languageSuffix = Mage::helper('bz_solr')->getLanguageSuffix($localeCode);
        //title and content heading are in 1 fulltext others in meta detail in 2 content in 3
        $indexData = array();
        foreach ($entityIndexData as $k => $v) {
            if ($k == 'title' || $k == 'content_heading')
                $fulltextData['fulltext_5' . $languageSuffix][] = $v;
            elseif ($k == 'meta_keywords' || $k == 'meta_description')
                $fulltextData['fulltext_4' . $languageSuffix][] = $v;
            else
                $fulltextData['fulltext_3' . $languageSuffix][] = $v;
            $spell['spell'.$languageSuffix][] = $v;
            $fieldName = $k . $languageSuffix;
            $indexData[$fieldName] = $v;
        }
        foreach ($fulltextData as $field => $data) {
            $indexData[$field] = implode(' ', $data);
        }
        foreach ($spell as $field => $data) {
            $indexData[$field] = implode(' ', $data);
        }
        //solr required schema stupid product schema
        $indexData['id'] = $entityId;
        $indexData['store_id'] = $storeId;
        $indexData['in_stock'] = false;
        $indexData['visibility'] = 4;
        //type data as default type data is product as we are not going to change enterprise product data
        $indexData['type'] = $entityType;
        //adding unqiue value
        $indexData[BZ_Solr_Model_Adapter_Abstract::UNIQUE_KEY] = $entityId . '|' . $storeId . '|' . $entityType;
        return $indexData;
    }
    
    public function cleanIndex($storeId, $cmsId, $entityType)
    {
        if ($this->_engine) {
            $this->_engine->cleanIndex($storeId, $cmsId, $entityType);
        }
        return $this;
    }
}
