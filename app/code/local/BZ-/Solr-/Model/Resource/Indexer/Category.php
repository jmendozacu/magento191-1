<?php
/**
 * Class Category
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Solr_Model_Resource_Indexer_Category extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_catNames = array();
    
    public function _construct() {
        $this->_engine = Mage::getResourceSingleton('bz_solr/engine');
    }
    
    public function rollBackChanges(){
        $this->_engine->rollBackChanges();
    }
    
    public function rebuildIndex($storeId = null, $entityIds = null, $entityType = 'category')
    {
        //do observer staff before index
        if (!$this->_engine->holdCommit()) {
            return;
        }
        //Index needs to be optimized if all entities of this type were affected
        if (is_null($entityIds)) {
            $this->_engine->setIndexNeedsOptimization();
        }
        //load all category names
        $this->loadCategoryNames();
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
    
    protected function _rebuildStoreIndex($storeId, $entityIds, $entityType = 'category')
    {
        //must provide $entityType or product will be clean
        $this->_engine->cleanIndex($storeId, $entityIds, $entityType);
        $p = 1;
        //define field index or need to be skip html coding
        $index_fields = array('name', 'meta_title', 'url_key', 'url_path', 'meta_keywords', 'meta_description');
        $trim_fields = array('description');
        while (true) {
            //load pages by default limit 100
            $categories = $this->_getSearchableEntityByLimit($storeId, $entityIds, $p);
            if (!$categories) {
                break;
            }
            $p++;
            //process and saving the first limit (100) data to solr
            foreach ($categories as $category) {
                $data = $category->getData();
                $index = array();
                foreach ($data as $k => $v) {
                    if (in_array($k, $trim_fields)) {
                        $index[$k] = $this->_engine->strip_html($v);
                    } elseif(in_array($k, $index_fields)){
                        $index[$k] = $v;
                    }
                }
                //adding name paths
                $names = array();
                $paths = explode('/', $category->getPath());
                $keys = array_keys($this->_catNames);
                $i = 0;
                foreach ($paths as $path) {
                    if ($i<2) {$i++; continue;}
                    if (in_array($path, $keys)) {
                        $names[] = $this->_catNames[$path];
                    }
                }
                if(!empty($names)) $index['name_path'] = implode(' > ', $names);
                //convert to solr fields
                $solr_index = $this->prepareIndexData($index, $category->getId(), $storeId, $entityType);
                $catIndexes[$category->getId()] = $solr_index;
            }
            //save data to solr by limits
            $this->_engine->saveEntityIndexes($storeId, $catIndexes, 'category');
        }
        return $this;
    }
    
    public function loadCategoryNames()
    {
        if(!$this->_catNames || empty($this->_catNames)){
            $categories = Mage::getResourceModel('catalog/category_collection')
                    ->addAttributeToSelect('name')
                    ->addFieldToFilter('is_active', 1);
            foreach($categories as $c){
                $this->_catNames[$c->getId()] = $c->getName();
            }
            $this->_catNames;
        }
        return $this->_catNames;
    }
    
    //as post to solr may have size limit so not too many docs send to solr at one http request
    protected function _getSearchableEntityByLimit($storeId, $entityIds, $p, $limit = 100)
    {
        $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
        $path = '1/'.$rootId.'/';
        $categories = Mage::getResourceModel('catalog/category_collection')
                      ->addAttributeToSelect('*')
                      ->addPathsFilter($path)
                      ->addAttributeToFilter('level',array('gt'=>1))
                      ->addAttributeToFilter('is_active', 1);
        $categories->getSelect()->limitPage($p, $limit);
        if(!is_null($entityIds) && is_array($entityIds) && !empty($entityIds))
            $categories->addFieldToFilter('entity_id',array('in'=>$entityIds));
        if(count($categories) > 0)
            return $categories;
        else
            return false;
    }
    
    public function prepareIndexData($entityIndexData, $entityId, $storeId, $entityType)
    {
        $fulltextData = array();
        $spell = array();
        $localeCode = Mage::app()->getStore($storeId)->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
        $languageSuffix = Mage::helper('bz_solr')->getLanguageSuffix($localeCode);
        //title and content heading are in 1 fulltext others in meta detail in 2 content in 3
        $indexData = array();
        $fulltext_5 = array('name', 'meta_title', 'url_key', 'url_path');
        $fulltext_4 = array('meta_keywords', 'meta_description');
        $fulltext_3 = array('description');
        $text_fields = array_merge($fulltext_3, $fulltext_4, $fulltext_5);
        $indexData['visibility'] = 1;
        foreach ($entityIndexData as $k => $v) {
            if (in_array($k, $fulltext_5))
                $fulltextData['fulltext_5' . $languageSuffix][] = $v;
            elseif (in_array($k, $fulltext_4))
                $fulltextData['fulltext_4' . $languageSuffix][] = $v;
            elseif (in_array($k, $fulltext_3))
                $fulltextData['fulltext_3' . $languageSuffix][] = $v;
            elseif ($k == 'is_active' && $v == 1)
                $indexData['visibility'] = 4;
            elseif ($k == 'name_path') $indexData['name_path'.$languageSuffix] = $v;
            if (in_array($k, $text_fields)) {
                $fieldName = $k . $languageSuffix;
                $indexData[$fieldName] = $v;
                $spell['spell'.$languageSuffix][] = $v;
            }
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
        //type data as default type data is product as we are not going to change enterprise product data
        $indexData['type'] = $entityType;
        //adding unqiue value
        $indexData[BZ_Solr_Model_Adapter_Abstract::UNIQUE_KEY] = $entityId . '|' . $storeId . '|' . $entityType;
        return $indexData;
    }
    
    public function cleanIndex($storeId, $catId, $entityType)
    {
        if ($this->_engine) {
            $this->_engine->cleanIndex($storeId, $catId, $entityType);
        }
        return $this;
    }
}