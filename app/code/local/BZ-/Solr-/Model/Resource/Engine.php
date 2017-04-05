<?php
/**
 * Class Fulltext
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Solr_Model_Resource_Engine
{
    //solr php client as php has extension for solr but require enable
    protected $_adapter = null;
    protected $_allowedEntityNames  = array();
    /**
     * Set search engine adapter
     */
    public function __construct()
    {
        $this->_initAdapter();
    }
    
    protected function _initAdapter()
    {
        $this->_adapter = $this->_getAdapterModel('solr');
        if (!$this->_canAllowCommit()) {
            $this->_adapter->holdCommit();
        }
        return $this;
    }
    
    //for advanced search
    public function getResourceName()
    {
        return 'bz_solr/advanced';
    }
    
    /**
     * Retrieve found document ids search index sorted by relevance
     *
     * @param string $query
     * @param array  $params see description in appropriate search adapter
     * @param string $entityType 'product'|'cms'
     * @return array
     */
    public function getIdsByQuery($query, $params = array(), $entityType = 'product')
    {   
        return $this->_adapter->getIdsByQuery($query, $params, $entityType);
    }
    
    public function getResultForRequest($query, $params = array(), $entityType = 'product')
    {
        return $this->_adapter->search($query, $params, $entityType);
    }
    
    public function getStats($query, $params = array(), $entityType = 'product')
    {
        return $this->_adapter->getStats($query, $params);
    }
    
    /**
     * Add entity data to search index
     *
     * @param int $entityId
     * @param int $storeId
     * @param array $index
     * @param string $entityType 'product'|'cms'|'category'
     *
     * @return this
     */
    public function saveEntityIndex($entityId, $storeId, $index, $entityType = 'product')
    {
        return $this->saveEntityIndexes($storeId, array($entityId => $index), $entityType);
    }

    /**
     * Add entities data to search index
     *
     * @param int $storeId
     * @param array $entityIndexes
     * @param string $entityType 'product'|'cms'|'category'
     *
     * @return this
     */
    public function saveEntityIndexes($storeId, $entityIndexes, $entityType = 'product')
    {
        if($storeId===0) return $this;
        if($entityType == 'product') $docs = $this->_adapter->prepareDocsPerStore($entityIndexes, $storeId, $entityType);
        else $docs = $this->_adapter->prepareDocsPerStoreNew($entityIndexes, $storeId, $entityType);
        $this->_adapter->addDocs($docs);

        return $this;
    }
    
    /**
     * Retrieve last query number of found results
     *
     * @return int
     */
    public function getLastNumFound()
    {
        return $this->_adapter->getLastNumFound();
    }

    /**
     * Retrieve search result data collection
     *
     * @return BZ_Solr_Model_Resource_Collection
     */
    public function getResultCollection()
    {
        return Mage::getResourceModel('bz_solr/collection')->setEngine($this);
    }
    
    /**
     * Retrieve advanced search result data collection
     */
    public function getAdvancedResultCollection()
    {
        return $this->getResultCollection();
    }

    /**
     * Retrieve allowed visibility values for current engine
     *
     * @see
     *
     * @return array
     */
    public function getAllowedVisibility()
    {
        return Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds();
    }

    /**
     * Prepare index array
     *
     * @param array $index
     * @param string $separator
     * @return array
     */
    public function prepareEntityIndex($index, $separator = null)
    {
        return $index;
    }

    /**
     * Retrieve search engine adapter model by adapter name
     * Now supporting only Solr search engine adapter
     *
     * @param string $adapterName
     * @return object
     */
    protected function _getAdapterModel($adapterName)
    {
        switch ($adapterName) {
            case 'solr':
            default:
                    $modelName = 'bz_solr/adapter_solr';
                break;
        }
        $adapter = Mage::getSingleton($modelName);
        return $adapter;
    }

    public function test()
    {
        return $this->_adapter->ping();
    }

    /**
     * Optimize search engine index
     *
     * @return BZ_Solr_Model_Resource_Engine
     */
    public function optimizeIndex()
    {
        $this->_adapter->optimize();
        return $this;
    }

    /**
     * Commit search engine index changes
     *
     * @return BZ_Solr_Model_Resource_Engine
     */
    public function commitChanges()
    {
        $this->_adapter->commit();
        return $this;
    }

    /**
     * adding rollback method
     */
    public function rollBackChanges()
    {
        $this->_adapter->rollBack();
        return $this;
    }

    /**
     * Hold commit of changes for adapter.
     * Can be used for one time commit after full indexation finish.
     * @return bool
     */
    public function holdCommit()
    {
        if ($this->_canHoldCommit()) {
            $this->_adapter->holdCommit();
            return true;
        }
        return false;
    }
    
    protected function _canHoldCommit()
    {
        return true;
    }
    
    public function allowCommit()
    {
        if ($this->_canAllowCommit()) {
            $this->_adapter->allowCommit();
            return true;
        }
        return false;
    }
    
    protected function _canAllowCommit()
    {
        return true;
    }

    /**
     * Define if third party search engine index needs optimization
     *
     * @param  bool $state
     * @return BZ_Solr_Model_Resource_Engine
     */
    public function setIndexNeedsOptimization($state = true)
    {
        $this->_adapter->setIndexNeedsOptimization($state);
        return $this;
    }

    /**
     * Check if third party search engine index needs optimization
     *
     * @return bool
     */
    public function getIndexNeedsOptimization()
    {
        return $this->_adapter->getIndexNeedsOptimization();
    }

    protected $_searchableAttributes = null;

    /**
     * Store searchable attributes
     *
     * @param array $attributes
     * @return BZ_Solr_Model_Resource_Engine
     */
    public function storeSearchableAttributes(array $attributes)
    {
        $this->_adapter->storeSearchableAttributes($attributes);
        return $this;
    }

    /**
     * Retrieve attribute field name for search engine
     *
     * @param   $attribute
     * @param   string $target
     *
     * @return  string|bool
     */
    public function getSearchEngineFieldName($attribute, $target = 'default')
    {
        return $this->_adapter->getSearchEngineFieldName($attribute, $target);
    }

    public function cleanIndex($storeIds = null, $entityIds = null, $entityType = 'product')
    {
        if ($storeIds === array() || $entityIds === array()) {
            return $this;
        }

        if (is_null($storeIds) || $storeIds == Mage_Core_Model_App::ADMIN_STORE_ID) {
            $storeIds = array_keys(Mage::app()->getStores());
        } else {
            $storeIds = (array) $storeIds;
        }

        $queries = array();
        if (empty($entityIds)) {
            foreach ($storeIds as $storeId) {
                //here we need to use type not store id as other entity also has store id
                $queries[] = 'type:'.$entityType .' AND store_id:'.$storeId;
            }
        } else {
            $entityIds = (array) $entityIds;
            $uniqueKey = $this->_adapter->getUniqueKey();
            foreach ($storeIds as $storeId) {
                foreach ($entityIds as $entityId) {
                    $queries[] = $uniqueKey . ':' . $entityId . '|' . $storeId. '|' .$entityType;
                }
            }
        }
        $this->_adapter->deleteDocs(array(), $queries);
        return $this;
    }
    
    public function allowAdvancedIndex()
    {
        return true;
    }
    
    public function strip_html($text) {
        $text = preg_replace(
                array(
            // Remove invisible content
            '@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<object[^>]*?.*?</object>@siu',
            '@<embed[^>]*?.*?</embed>@siu',
            '@<applet[^>]*?.*?</applet>@siu',
            '@<noframes[^>]*?.*?</noframes>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
            '@<noembed[^>]*?.*?</noembed>@siu',
            '@{{[^}}]*?.*?}}@siu',
            // Add line breaks before and after blocks
            '@</?((address)|(blockquote)|(center)|(del))@iu',
            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
            '@</?((table)|(th)|(td)|(caption))@iu',
            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
            '@</?((frameset)|(frame)|(iframe))@iu',
                ), array(
            '', '', '', '', '', '', '', '', '', '',"$0", "$0", "$0", "$0", "$0", "$0", "$0", "$0", "$0"), $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text);
        return preg_replace('/\s+/', ' ', $text);
    }
    
    //Magento developer typo
    public function isLeyeredNavigationAllowed()
    {
        return true;
    }

}
