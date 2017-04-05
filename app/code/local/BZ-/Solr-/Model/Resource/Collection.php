<?php
/**
 * Class Collection for solr as it is not database
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Solr_Model_Resource_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    protected $_searchQueryText = '';

    protected $_searchQueryParams = array();

    protected $_searchQueryFilters = array();

    protected $_searchedEntityIds = array();

    protected $_searchedSuggestions = array();

    protected $_engine = null;

    protected $_sortBy = array();


    /**
     * General default query *:* to disable query limitation
     * @var array
     */
    protected $_generalDefaultQuery = array('*' => '*');

    protected $_facetedDataIsLoaded = false;

    protected $_facetedData = array();

    protected $_suggestionsData = array();

    protected $_facetedConditions = array();

    protected $_storedPageSize = false;

    public function loadFacetedData()
    {
        if (empty($this->_facetedConditions)) {
            $this->_facetedData = array();
            return $this;
        }
        list($query, $params) = $this->_prepareBaseParams();
        $params['solr_params']['facet'] = 'on';
        $params['facet'] = $this->_facetedConditions;
        $result = $this->_engine->getResultForRequest($query, $params);
        $this->_facetedData = $result['faceted_data'];
        $this->_facetedDataIsLoaded = true;
        return $this;
    }
    
    public function getFacetedData($field)
    {
        if (!$this->_facetedDataIsLoaded) {
            $this->loadFacetedData();
        }
        if (isset($this->_facetedData[$field])) {
            return $this->_facetedData[$field];
        }
        return array();
    }
    
    //support multiple select
    public function getFacetedDataWithoutSelf($field)
    {
        if (empty($this->_facetedConditions)) {
            $this->_facetedData = array();
            return $this;
        }
        list($query, $params) = $this->_prepareBaseParams();
        $params['solr_params']['facet'] = 'on';
        $params['facet'] = $this->_facetedConditions;
        if (isset($params['filters']) && !empty($params['filters'])) {
            foreach ($params['filters'] as $k => $v) {
                if($k == $field){
                    unset($params['filters'][$k]);
                    break;
                }
            }
        }
        $result = $this->_engine->getResultForRequest($query, $params);
        $arr = $result['faceted_data'];
        if(isset($arr[$field])) return $arr[$field];
        return array();
    }

    public function getSuggestionsData()
    {   
        return $this->_suggestionsData;
    }

    public function setFacetCondition($field, $condition = null)
    {
        if (array_key_exists($field, $this->_facetedConditions)) {
            if (!empty($this->_facetedConditions[$field])) {
                $this->_facetedConditions[$field] = array($this->_facetedConditions[$field]);
            }
            $this->_facetedConditions[$field][] = $condition;
        } else {
            $this->_facetedConditions[$field] = $condition;
        }
        $this->_facetedDataIsLoaded = false;
        return $this;
    }

    public function addSearchFilter($queryText)
    {
        /**
         * @var Mage_CatalogSearch_Model_Query $query
         */
        $query = Mage::helper('catalogsearch')->getQuery();
        $this->_searchQueryText = $queryText;
        $synonymFor = $query->getSynonymFor();
        if (!empty($synonymFor)) {
            $this->_searchQueryText .= ' ' . $synonymFor;
        }
        return $this;
    }

    public function addSearchParam($param, $value = null)
    {
        if (is_array($param)) {
            foreach ($param as $field => $value) {
                $this->addSearchParam($field, $value);
            }
        } elseif (!empty($value)) {
            $this->_searchQueryParams[$param] = $value;
        }
        return $this;
    }

    public function getExtendedSearchParams()
    {
        $result = $this->_searchQueryFilters;
        $result['query_text'] = $this->_searchQueryText;
        return $result;
    }

    /**
     * Add search query filter (fq)
     */
    public function addFqFilter($param)
    {
        if (is_array($param)) {
            foreach ($param as $field => $value) {
                $this->_searchQueryFilters[$field] = $value;
            }
        }
        return $this;
    }

    /**
     * Add advanced search query filter
     * Set search query
     */
    public function addAdvancedSearchFilter($query)
    {
        return $this->addSearchFilter($query);
    }

    /**
     * Specify category filter for product collection
     * @param   Mage_Catalog_Model_Category $category
     * @return  this
     */
    public function addCategoryFilter(Mage_Catalog_Model_Category $category)
    {
        $this->addFqFilter(array('category_ids' => $category->getId()));
        //parent::addCategoryFilter($category); copy this function here
        $this->_productLimitationFilters['category_id'] = $category->getId();
        if ($category->getIsAnchor()) {
            unset($this->_productLimitationFilters['category_is_anchor']);
        } else {
            $this->_productLimitationFilters['category_is_anchor'] = 1;
        }

        if ($this->getStoreId() == Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
            $this->_applyZeroStoreProductLimitations();
        } else {
            $this->_applyProductLimitations();
        }

        return $this;
    }
    
    // as Magento do not allow categories not anchor to show in search result page but solr allow
    protected function _applyProductLimitations()
    {
        $this->_prepareProductLimitationFilters();
        $this->_productLimitationJoinWebsite();
        $this->_productLimitationJoinPrice();
        $filters = $this->_productLimitationFilters;

        if (!isset($filters['category_id']) && !isset($filters['visibility'])) {
            return $this;
        }

        $conditions = array(
            'cat_index.product_id=e.entity_id',
            $this->getConnection()->quoteInto('cat_index.store_id=?', $filters['store_id'])
        );
        if (isset($filters['visibility']) && !isset($filters['store_table'])) {
            $conditions[] = $this->getConnection()
                ->quoteInto('cat_index.visibility IN(?)', $filters['visibility']);
        }
        $conditions[] = $this->getConnection()
            ->quoteInto('cat_index.category_id=?', $filters['category_id']);

        $joinCond = join(' AND ', $conditions);
        $fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
        if (isset($fromPart['cat_index'])) {
            $fromPart['cat_index']['joinCondition'] = $joinCond;
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
        }
        else {
            $this->getSelect()->join(
                array('cat_index' => $this->getTable('catalog/category_product_index')),
                $joinCond,
                array('cat_index_position' => 'position')
            );
        }

        $this->_productLimitationJoinStore();
        Mage::dispatchEvent('catalog_product_collection_apply_limitations_after', array(
            'collection'    => $this
        ));

        return $this;
    }
    
    /**
     * Add sort order
     * @param string $attribute
     * @param string $dir
     * @return this
     */
    public function setOrder($attribute, $dir = 'desc')
    {
        $this->_sortBy[] = array($attribute => $dir);
        return $this;
    }

    protected function _prepareBaseParams()
    {
        $store  = Mage::app()->getStore();
        $params = array(
            'store_id'      => $store->getId(),
            'locale_code'   => $store->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE),
            'filters'       => $this->_searchQueryFilters
        );
        $params['filters']     = $this->_searchQueryFilters;
        if (!empty($this->_searchQueryParams)) {
            $params['ignore_handler'] = true;
            $query = $this->_searchQueryParams;
        } else {
            $query = $this->_searchQueryText;
        }
        return array($query, $params);
    }

    /**
     * Search documents by query
     * Set found ids and number of found results
     */
    protected function _beforeLoad()
    {
        $query = Mage::helper('catalogsearch')->getQuery();
        $term = Mage::helper('catalogsearch')->getQueryText();
        $query = Mage::getModel('catalogsearch/query')->setQueryText($term)->prepare();
        $fulltextResource = Mage::getResourceModel('catalogsearch/fulltext')->prepareResult(
            Mage::getModel('catalogsearch/fulltext'),
            $term,
            $query
        );

        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->getSelect()->joinInner(
            array('search_result' => $collection->getTable('catalogsearch/result')),
            $collection->getConnection()->quoteInto(
                'search_result.product_id=e.entity_id AND search_result.query_id=?',
                $query->getId()
            ),
            array('relevance' => 'relevance')
        );

        $productIds = array();
        $productIds = $collection->getAllIds(); // as per Amit Bera s' comment
        $ids = array();
        if ($this->_engine) {
            list($query, $params) = $this->_prepareBaseParams();
            if ($this->_sortBy) {
                $params['sort_by'] = $this->_sortBy;
            }
            if ($this->_pageSize !== false) {
                $page              = ($this->_curPage  > 0) ? (int) $this->_curPage  : 1;
                $rowCount          = ($this->_pageSize > 0) ? (int) $this->_pageSize : 1;
                $params['offset']  = $rowCount * ($page - 1);
                $params['limit']   = $rowCount;
            }

            $needToLoadFacetedData = (!$this->_facetedDataIsLoaded && !empty($this->_facetedConditions));
            if ($needToLoadFacetedData) {
                $params['solr_params']['facet'] = 'on';
                $params['facet'] = $this->_facetedConditions;
            }
            $result = $this->_engine->getIdsByQuery($query, $params);
            $ids    = (array) $result['ids'];
            if ($needToLoadFacetedData) {
                $this->_facetedData = $result['faceted_data'];
                $this->_facetedDataIsLoaded = true;
            }
            $this->_searchedEntityIds = &$ids;

        }
        $this->getSelect()->where('e.entity_id IN (?)', $this->_searchedEntityIds);
        /**
         * To prevent limitations to the collection, because of new data logic.
         * On load collection will be limited by _pageSize and appropriate offset,
         * but third party search engine retrieves already limited ids set
         */
        $this->_storedPageSize = $this->_pageSize;
        $this->_pageSize = false;
        return parent::_beforeLoad();
    }

    /**
     * Sort collection items by sort order of found ids
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $sortedItems = array();
        foreach ($this->_searchedEntityIds as $id) {
            if (isset($this->_items[$id])) {
                $sortedItems[$id] = $this->_items[$id];
            }
        }
        $this->_items = &$sortedItems;
        /**
         * Revert page size for proper paginator ranges
         */
        $this->_pageSize = $this->_storedPageSize;
        return $this;
    }

    /**
     * Retrieve found number of items
     * @return int
     */
    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            list($query, $params) = $this->_prepareBaseParams();
            $params['limit'] = 1;

            $helper = Mage::helper('bz_solr');
            $searchSuggestionsEnabled = ($this->_searchQueryParams != $this->_generalDefaultQuery
                    && $helper->getSolrConfigData('server_suggestion_enabled'));
            if ($searchSuggestionsEnabled) {
                $params['solr_params']['spellcheck'] = 'true';
                $searchSuggestionsCount = (int) $helper->getSolrConfigData('server_suggestion_count');
                $params['solr_params']['spellcheck.count']  = $searchSuggestionsCount;
                $params['spellcheck_result_counts']         = (bool) $helper->getSolrConfigData(
                    'server_suggestion_count_results_enabled');
            }
            $result = $this->_engine->getIdsByQuery($query, $params);
            if ($searchSuggestionsEnabled && !empty($result['suggestions_data'])) {
                $this->_suggestionsData = $result['suggestions_data'];
            }
            $this->_totalRecords = $this->_engine->getLastNumFound();
        }
        return $this->_totalRecords;
    }

    public function getStats($fields)
    {
        list($query, $params) = $this->_prepareBaseParams();
        $params['limit'] = 0;
        $params['solr_params']['stats'] = 'true';
        if (!is_array($fields)) {
            $fields = array($fields);
        }
        foreach ($fields as $field) {
            $params['solr_params']['stats.field'][] = $field;
        }

        return $this->_engine->getStats($query, $params);
    }
    
    /**
     * price slider need to know the clean max price and min price
     */
    public function getOriginalPriceStats($fields)
    {
        list($query, $params) = $this->_prepareBaseParams();
        $params['limit'] = 0;
        $params['solr_params']['stats'] = 'true';
        if (!is_array($fields)) {
            $fields = array($fields);
        }
        //clean filters fields
        if (isset($params['filters']) && !empty($params['filters'])) {
            $catalog_fields = array('category_ids','visibility');
            foreach ($params['filters'] as $key => $field) {
                if(!in_array($key, $catalog_fields)) unset($params['filters'][$key]);
            }
        }
        foreach ($fields as $field) {
            $params['solr_params']['stats.field'][] = $field;
        }
        return $this->_engine->getStats($query, $params);
    }

    /**
     * Set query *:* to disable query limitation
     */
    public function setGeneralDefaultQuery()
    {
        $this->_searchQueryParams = $this->_generalDefaultQuery;
        return $this;
    }

    /**
     * Set search engine
     */
    public function setEngine($engine)
    {
        $this->_engine = $engine;
        return $this;
    }

    /**
     * Stub method
     */
    public function addFieldsToFilter($fields)
    {
        return $this;
    }

    public function addCountToCategories($categoryCollection)
    {
        return $this;
    }

    /**
     * Set product visibility filter for enabled products
     */
    public function setVisibility($visibility)
    {
        if (is_array($visibility)) {
            $this->addFqFilter(array('visibility' => $visibility));
        }

        return $this;
    }

    /**
     * Get prices from search results
     *
     * @param   null|float $lowerPrice
     * @param   null|float $upperPrice
     * @param   null|int   $limit
     * @param   null|int   $offset
     * @param   boolean    $getCount
     * @param   string     $sort
     * @return  array
     */
    public function getPriceData($lowerPrice = null, $upperPrice = null,
        $limit = null, $offset = null, $getCount = false, $sort = 'asc')
    {
        list($query, $params) = $this->_prepareBaseParams();
        $priceField = $this->_engine->getSearchEngineFieldName('price');
        $conditions = null;
        if (!is_null($lowerPrice) || !is_null($upperPrice)) {
            $conditions = array();
            $conditions['from'] = is_null($lowerPrice) ? 0 : $lowerPrice;
            $conditions['to'] = is_null($upperPrice) ? '' : $upperPrice;
        }
        if (!$getCount) {
            $params['fields'] = $priceField;
            $params['sort_by'] = array(array('price' => $sort));
            if (!is_null($limit)) {
                $params['limit'] = $limit;
            }
            if (!is_null($offset)) {
                $params['offset'] = $offset;
            }
            if (!is_null($conditions)) {
                $params['filters'][$priceField] = $conditions;
            }
        } else {
            $params['solr_params']['facet'] = 'on';
            if (is_null($conditions)) {
                $conditions = array('from' => 0, 'to' => '');
            }
            $params['facet'][$priceField] = array($conditions);
        }

        $data = $this->_engine->getResultForRequest($query, $params);
        if ($getCount) {
            return array_shift($data['faceted_data'][$priceField]);
        }
        $result = array();
        foreach ($data['ids'] as $value) {
            $result[] = (float)$value[$priceField];
        }

        return ($sort == 'asc') ? $result : array_reverse($result);
    }
}
