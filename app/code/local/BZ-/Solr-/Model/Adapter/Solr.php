<?php
/**
 * Class Solr http solr client not php solr extension
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Solr_Model_Adapter_Solr extends BZ_Solr_Model_Adapter_Abstract
{
    /**
     * Default number of rows to select
     */
    const DEFAULT_ROWS_LIMIT        = 9999;

    /**
     * Default suggestions count
     */
    const DEFAULT_SPELLCHECK_COUNT  = 1;

    /**
     * Define ping status
     *
     * @var float | bool
     */
    protected $_ping = null;

    /**
     * Array of Zend_Date objects per store
     *
     * @var array
     */
    protected $_dateFormats = array();

    /**
     * Advanced index fields prefix
     *
     * @var string
     */
    protected $_advancedIndexFieldsPrefix = '';
    
    public function __construct($options = array())
    {
        try {
            $this->_connect($options);
        } catch (Exception $e){
            Mage::logException($e);
        }
    }

    /**
     * Set advanced index fields prefix
     *
     * @param string $prefix
     */
    public function setAdvancedIndexFieldPrefix($prefix)
    {
        $this->_advancedIndexFieldsPrefix = $prefix;
    }

    /**
     * Retrieve language code by specified locale code if this locale is supported by Solr
     *
     * @param string $localeCode
     * @return false|string
     */
    protected function _getLanguageCodeByLocaleCode($localeCode)
    {
        return Mage::helper('bz_solr')->getLanguageCodeByLocaleCode($localeCode);
    }

    /**
     * Prepare language suffix for text fields.
     * For not supported languages prefix _def will be returned.
     *
     * @param  string $localeCode
     * @return string
     */
    protected function _getLanguageSuffix($localeCode)
    {
        return Mage::helper('bz_solr')->getLanguageSuffix($localeCode);
    }
    
    /**
     * implement connect function 
     */
    protected function _connect($options = array())
    {
        $helper = Mage::helper('bz_solr');
        $def_options = array(
            'hostname' => $helper->getSolrConfigData('server_hostname'),
            'login'    => $helper->getSolrConfigData('server_username'),
            'password' => $helper->getSolrConfigData('server_password'),
            'port'     => $helper->getSolrConfigData('server_port'),
            'timeout'  => $helper->getSolrConfigData('server_timeout'),
            'path'     => $helper->getSolrConfigData('server_path')
        );
        $options = array_merge($def_options, $options);

        try {
            $this->_client = Mage::getSingleton('bz_solr/search_client_solr', $options);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this->_client;
    }
    
    /**
     * implement search function 
     */
    protected function _search($query, $params = array(), $searchType='product')
    {
        $searchConditions = $this->prepareSearchConditions($query);
        if (!$searchConditions) {
            return array();
        }

        $_params = $this->_defaultQueryParams;
        if (is_array($params) && !empty($params)) {
            $_params = array_intersect_key($params, $_params) + array_diff_key($_params, $params);
        }

        $offset = (isset($_params['offset'])) ? (int) $_params['offset'] : 0;
        $limit  = (isset($_params['limit']))
            ? (int) $_params['limit']
            : BZ_Solr_Model_Adapter_Solr::DEFAULT_ROWS_LIMIT;

        $languageSuffix = $this->_getLanguageSuffix($params['locale_code']);
        $searchParams   = array();

        if (!is_array($_params['fields'])) {
            $_params['fields'] = array($_params['fields']);
        }

        if (!is_array($_params['solr_params'])) {
            $_params['solr_params'] = array($_params['solr_params']);
        }

        /**
         * Add sort fields
         */
        $sortFields = $this->_prepareSortFields($_params['sort_by']);
        foreach ($sortFields as $sortField) {
            $searchParams['sort'][] = $sortField['sortField'] . ' ' . $sortField['sortType'];
        }

        /**
         * Fields to retrieve
         */
        //adding default unique field
        if(!empty($_params['fields']) && !in_array($this::UNIQUE_KEY, $_params['fields'])) $_params['fields'][] = $this::UNIQUE_KEY;
        if(!empty($_params['fields']) && !in_array('type', $_params['fields'])) $_params['fields'][] = 'type';
        if(!empty($_params['fields']) && !in_array('id', $_params['fields'])) $_params['fields'][] = 'id';
        if ($limit && !empty($_params['fields'])) {
            $searchParams['fl'] = implode(',', $_params['fields']);
        }

        /**
         * Now supported search only in fulltext and name fields based on dismax requestHandler (named as magento_lng).
         * Using dismax requestHandler for each language make matches in name field
         * are much more significant than matches in fulltext field.
         */
        if ($_params['ignore_handler'] !== true) {
            $_params['solr_params']['qt'] = 'magento' . $languageSuffix;
        }

        /**
         * Facets search
         */
        $useFacetSearch = (isset($params['solr_params']['facet']) && $params['solr_params']['facet'] == 'on');
        if ($useFacetSearch) {
            $searchParams += $this->_prepareFacetConditions($params['facet']);
        }

        /**
         * Suggestions search
         */
        $useSpellcheckSearch = isset($params['solr_params']['spellcheck'])
            && $params['solr_params']['spellcheck'] == 'true';

        if ($useSpellcheckSearch) {
            if (isset($params['solr_params']['spellcheck.count'])
                && (int) $params['solr_params']['spellcheck.count'] > 0
            ) {
                $spellcheckCount = (int) $params['solr_params']['spellcheck.count'];
            } else {
                $spellcheckCount = self::DEFAULT_SPELLCHECK_COUNT;
            }

            $_params['solr_params'] += array(
                'spellcheck.collate'         => 'true',
                'spellcheck.dictionary'      => 'magento_spell' . $languageSuffix,
                'spellcheck.extendedResults' => 'true',
                'spellcheck.count'           => $spellcheckCount
            );
        }

        /**
         * Specific Solr params
         */
        if (!empty($_params['solr_params'])) {
            foreach ($_params['solr_params'] as $name => $value) {
                $searchParams[$name] = $value;
            }
        }

        $searchParams['fq'] = $this->_prepareFilters($_params['filters']);

        /**
         * Store filtering
         */
        if ($_params['store_id'] > 0) {
            $searchParams['fq'][] = 'store_id:' . $_params['store_id'];
        }
        if (!Mage::helper('cataloginventory')->isShowOutOfStock()) {
            $searchParams['fq'][] = 'in_stock:true';
        }
        if($searchType && !empty($searchType)) $searchParams['fq'][] = 'type:'.$searchType;

        $searchParams['fq'] = implode(' AND ', $searchParams['fq']);

        try {
            $this->ping();
            $response = $this->_client->search(
                $searchConditions, $offset, $limit, $searchParams, Apache_Solr_Service::METHOD_POST
            );
            $data = json_decode($response->getRawResponse());
            $result = array();
            if (!isset($params['solr_params']['stats']) || $params['solr_params']['stats'] != 'true') {
                if ($limit > 0) {
                    $queryResult = $this->_prepareQueryResponse($data);
                    if(isset($queryResult['product'])){
                        $ids = array();
                        foreach($queryResult['product'] as $p){
                            $ids[] = $p['id'];
                        }
                        $result = array('ids' => $ids);
                    }
                }

                /**
                 * Extract facet search results
                 */
                if ($useFacetSearch) {
                    $result['faceted_data'] = $this->_prepareFacetsQueryResponse($data);
                }

                /**
                 * Extract suggestions search results
                 */
                if ($useSpellcheckSearch) {
                    $resultSuggestions = $this->_prepareSuggestionsQueryResponse($data);
                    /* Calc results count for each suggestion */
                    if (isset($params['spellcheck_result_counts']) && $params['spellcheck_result_counts']
                        && count($resultSuggestions)
                        && $spellcheckCount > 0
                    ) {
                        /* Temporary store value for main search query */
                        $tmpLastNumFound = $this->_lastNumFound;

                        unset($params['solr_params']['spellcheck']);
                        unset($params['solr_params']['spellcheck.count']);
                        unset($params['spellcheck_result_counts']);

                        $suggestions = array();
                        foreach ($resultSuggestions as $key => $item) {
                            $this->_lastNumFound = 0;
                            //internal search to get product counts
                            $this->search($item['word'], $params);
                            if ($this->_lastNumFound) {
                                $resultSuggestions[$key]['num_results'] = $this->_lastNumFound;
                                $suggestions[] = $resultSuggestions[$key];
                                $spellcheckCount--;
                            }
                            if ($spellcheckCount <= 0) {
                                break;
                            }
                        }

                        /* Return store value for main search query */
                        $this->_lastNumFound = $tmpLastNumFound;
                    } else {
                        $suggestions = array_slice($resultSuggestions, 0, $spellcheckCount);
                    }
                    $result['suggestions_data'] = $suggestions;
                }
            } else {
                $result = $this->_prepateStatsQueryResponce($data);
            }

            return $result;
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Retrieve date value in solr format (ISO 8601) with Z
     * Example: 1995-12-31T23:59:59Z
     *
     * @param int $storeId
     * @param string $date
     *
     * @return string|null
     */
    protected function _getSolrDate($storeId, $date = null)
    {
        if (!isset($this->_dateFormats[$storeId])) {
            $timezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE, $storeId);
            $locale   = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $storeId);
            $locale   = new Zend_Locale($locale);

            $dateObj  = new Zend_Date(null, null, $locale);
            $dateObj->setTimezone($timezone);
            $this->_dateFormats[$storeId] = array($dateObj, $locale->getTranslation(null, 'date', $locale));
        }

        if (is_empty_date($date)) {
            return null;
        }

        list($dateObj, $localeDateFormat) = $this->_dateFormats[$storeId];
        $dateObj->setDate($date, $localeDateFormat);

        return $dateObj->toString(Zend_Date::ISO_8601) . 'Z';
    }

    /**
     * Prepare search conditions from query
     *
     * @param string|array $query
     *
     * @return string
     */
    protected function prepareSearchConditions($query)
    {
        if (is_array($query)) {
            $searchConditions = array();
            foreach ($query as $field => $value) {
                if (is_array($value)) {
                    if ($field == 'price' || isset($value['from']) || isset($value['to'])) {
                        $from = (isset($value['from']) && strlen(trim($value['from'])))
                            ? $this->_escape($value['from']) : '*';
                        $to = (isset($value['to']) && strlen(trim($value['to'])))
                            ? $this->_escape($value['to']) : '*';
                        $fieldCondition = "$field:[$from TO $to]";
                    } else {
                        $fieldCondition = array();
                        foreach ($value as $part) {
                            $part = $this->_prepareFilterQueryText($part);
                            $fieldCondition[] = $field .':'. $part;
                        }
                        $fieldCondition = '('. implode(' OR ', $fieldCondition) .')';
                    }
                } else {
                    if ($value != '*') {
                        $value = $this->_prepareQueryText($value);
                    }
                    $fieldCondition = $field .':'. $value;
                }

                $searchConditions[] = $fieldCondition;
            }

            $searchConditions = implode(' AND ', $searchConditions);
        } else {
            $searchConditions = $this->_prepareQueryText($query);
        }

        return $searchConditions;
    }

    /**
     * Prepare facet fields conditions
     *
     * @param array $facetFields
     * @return array
     */
    protected function _prepareFacetConditions($facetFields)
    {
        $result = array();

        if (is_array($facetFields)) {
            $result['facet'] = 'on';
            foreach ($facetFields as $facetField => $facetFieldConditions) {
                if (empty($facetFieldConditions)) {
                    $result['facet.field'][] = $facetField;
                } else {
                    foreach ($facetFieldConditions as $facetCondition) {
                        if (is_array($facetCondition) && isset($facetCondition['from'])
                                && isset($facetCondition['to'])) {
                            $from = strlen(trim($facetCondition['from']))
                                ? $facetCondition['from']
                                : '*';
                            $to = strlen(trim($facetCondition['to']))
                                ? $facetCondition['to']
                                : '*';
                            $fieldCondition = "$facetField:[$from TO $to]";
                        } else {
                            $facetCondition = $this->_prepareQueryText($facetCondition);
                            $fieldCondition = $this->_prepareFieldCondition($facetField, $facetCondition);
                        }

                        $result['facet.query'][] = $fieldCondition;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Prepare fq filter conditions
     *
     * @param array $filters
     * @return array
     */
    protected function _prepareFilters($filters)
    {
        $result = array();

        if (is_array($filters) && !empty($filters)) {
            foreach ($filters as $field => $value) {
                if (is_array($value)) {
                    if (isset($value['from']) || isset($value['to'])) {
                        $from = (isset($value['from']) && !empty($value['from']))
                            ? $value['from']
                            : '*';
                        $to = (isset($value['to']) && !empty($value['to']))
                            ? $value['to']
                            : '*';
                        $fieldCondition = "$field:[$from TO $to]";
                    } else {
                        $fieldCondition = array();
                        foreach ($value as $part) {
                            $part = $this->_prepareFilterQueryText($part);
                            $fieldCondition[] = $this->_prepareFieldCondition($field, $part);
                        }
                        $fieldCondition = '(' . implode(' OR ', $fieldCondition) . ')';
                    }
                } else {
                    $value = $this->_prepareFilterQueryText($value);
                    $fieldCondition = $this->_prepareFieldCondition($field, $value);
                }

                $result[] = $fieldCondition;
            }
        }

        return $result;
    }

    /**
     * Prepare sort fields
     *
     * @param array $sortBy
     * @return array
     */
    protected function _prepareSortFields($sortBy)
    {
        $result = array();

        $localeCode = Mage::app()->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
        $languageSuffix = $this->_getLanguageSuffix($localeCode);

        /**
         * Support specifying sort by field as only string name of field
         */
        if (!empty($sortBy) && !is_array($sortBy)) {
            if ($sortBy == 'relevance') {
                $sortBy = 'score';
            } elseif ($sortBy == 'name') {
                $sortBy = 'alphaNameSort' . $languageSuffix;
            } elseif ($sortBy == 'position') {
                $sortBy = 'position_category_' . Mage::registry('current_category')->getId();
            } elseif ($sortBy == 'price') {
                $websiteId       = Mage::app()->getStore()->getWebsiteId();
                $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();

                $sortBy = 'price_'. $customerGroupId .'_'. $websiteId;
            }

            $sortBy = array(array($sortBy => 'asc'));
        }

        foreach ($sortBy as $sort) {
            $_sort = each($sort);
            $sortField = $_sort['key'];
            $sortType = $_sort['value'];
            if ($sortField == 'relevance') {
                $sortField = 'score';
            } elseif ($sortField == 'position') {
                $sortField = 'position_category_' . Mage::registry('current_category')->getId();
            } elseif ($sortField == 'price') {
                $sortField = $this->getPriceFieldName();
            } else {
                $sortField = $this->getSearchEngineFieldName($sortField, 'sort');
            }

            $result[] = array('sortField' => $sortField, 'sortType' => trim(strtolower($sortType)));
        }

        return $result;
    }

    /**
     * Retrieve Solr server status
     *
     * @return  float|bool Actual time taken to ping the server, FALSE if timeout or HTTP error status occurs
     */
    public function ping()
    {
        if (is_null($this->_ping)) {
            try {
                $this->_ping = $this->_client->ping();
            } catch (Exception $e) {
                $this->_ping = false;
            }
        }

        return $this->_ping;
    }

    /**
     * Prepare name for system text fields.
     *
     * @param   string $filed
     * @param   string $suffix
     * @return  string
     */
    public function getAdvancedTextFieldName($filed, $suffix = '', $storeId = null)
    {
        $localeCode     = Mage::app()->getStore($storeId)->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
        $languageSuffix = Mage::helper('bz_solr')->getLanguageSuffix($localeCode);

        if ($suffix) {
            $suffix = '_' . $suffix;
        }

        return $filed . $suffix . $languageSuffix;
    }

    /**
     * Retrieve attribute solr field name
     *
     * @param   Mage_Catalog_Model_Resource_Eav_Attribute|string $attribute
     * @param   string $target - default|sort|nav
     *
     * @return  string|bool
     */
    public function getSearchEngineFieldName($attribute, $target = 'default')
    {
        if (is_string($attribute)) {
            if ($attribute == 'price') {
                return $this->getPriceFieldName();
            }

            $eavConfig  = Mage::getSingleton('eav/config');
            $entityType = $eavConfig->getEntityType('catalog_product');
            $attribute  = $eavConfig->getAttribute($entityType, $attribute);
        }

        // Field type defining
        $attributeCode = $attribute->getAttributeCode();
        if (in_array($attributeCode, array('sku'))) {
            return $attributeCode;
        }

        if ($attributeCode == 'price') {
            return $this->getPriceFieldName();
        }

        $backendType    = $attribute->getBackendType();
        $frontendInput  = $attribute->getFrontendInput();

        if ($frontendInput == 'multiselect') {
            $fieldType = 'multi';
        } elseif ($frontendInput == 'select' || $frontendInput == 'boolean') {
            $fieldType = 'select';
        } elseif ($backendType == 'decimal' || $backendType == 'datetime') {
            $fieldType = $backendType;
        } else {
            $fieldType = 'text';
        }

        // Field prefix construction. Depends on field usage purpose - default, sort, navigation
        $fieldPrefix = 'attr_';
        if ($target == 'sort') {
            $fieldPrefix .= $target . '_';
        } elseif ($target == 'nav') {
            if ($attribute->getIsFilterable() || $attribute->getIsFilterableInSearch() || $attribute->usesSource()) {
                $fieldPrefix .= $target . '_';
            }
        }

        if ($fieldType == 'text') {
            $localeCode     = Mage::app()->getStore($attribute->getStoreId())
                ->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
            $languageSuffix = Mage::helper('bz_solr')->getLanguageSuffix($localeCode);
            $fieldName      = $fieldPrefix . $attributeCode . $languageSuffix;
        } else {
            $fieldName      = $fieldPrefix . $fieldType . '_' . $attributeCode;
        }

        return $fieldName;
    }
    
    protected function _prepareIndexProductData($productIndexData, $productId, $storeId)
    {
        if (!$this->isAvailableInIndex($productIndexData, $productId)) {
            return false;
        }
        $fulltextData = array();
        foreach ($productIndexData as $attributeCode => $value) {

            if ($attributeCode == 'visibility') {
                $productIndexData[$attributeCode] = $value[$productId];
                continue;
            }

            // Prepare processing attribute info
            if (isset($this->_indexableAttributeParams[$attributeCode])) {
                /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                $attribute = $this->_indexableAttributeParams[$attributeCode];
            } else {
                $attribute = null;
            }

            // Prepare values for required fields
            if (!in_array($attributeCode, $this->_usedFields)) {
                unset($productIndexData[$attributeCode]);
            }

            if (!$attribute || $attributeCode == 'price' || empty($value)) {
                continue;
            }

            $attribute->setStoreId($storeId);

            // Preparing data for solr fields
            if ($attribute->getIsSearchable() || $attribute->getIsVisibleInAdvancedSearch()
                || $attribute->getIsFilterable() || $attribute->getIsFilterableInSearch()
            ) {
                $backendType = $attribute->getBackendType();
                $frontendInput = $attribute->getFrontendInput();

                if ($attribute->usesSource()) {
                    if ($frontendInput == 'multiselect') {
                        $preparedValue = array();
                        foreach ($value as $val) {
                            $preparedValue = array_merge($preparedValue, explode(',', $val));
                        }
                        $preparedNavValue = $preparedValue;
                    } else {
                        // safe condition
                        if (!is_array($value)) {
                            $preparedValue = array($value);
                        } else {
                            $preparedValue = array_unique($value);
                        }

                        $preparedNavValue = $preparedValue;
                        // Ensure that self product value will be saved after array_unique function for sorting purpose
                        if (isset($value[$productId])) {
                            if (!isset($preparedNavValue[$productId])) {
                                $selfValueKey = array_search($value[$productId], $preparedNavValue);
                                unset($preparedNavValue[$selfValueKey]);
                                $preparedNavValue[$productId] = $value[$productId];
                            }
                        }
                    }

                    foreach ($preparedValue as $id => $val) {
                        //in ce1.8+ we can use $attribute->getSource()->getIndexOptionText($val); for Boolean value Yes and No rather than 1 and 0
                        //$preparedValue[$id] = $attribute->getSource()->getIndexOptionText($val);
                        $preparedValue[$id] = $attribute->getSource()->getOptionText($val);
                    }
                } else {
                    $preparedValue = $value;
                    if ($backendType == 'datetime') {
                        if (is_array($value)) {
                            $preparedValue = array();
                            foreach ($value as &$val) {
                                $val = $this->_getSolrDate($storeId, $val);
                                if (!empty($val)) {
                                    $preparedValue[] = $val;
                                }
                            }
                            unset($val); //clear link to value
                            $preparedValue = array_unique($preparedValue);
                        } else {
                            $preparedValue = $this->_getSolrDate($storeId, $value);
                        }
                    }
                }
            }

            // Preparing data for sorting field
            if ($attribute->getUsedForSortBy()) {
                if (is_array($preparedValue)) {
                    if (isset($preparedValue[$productId])) {
                        $sortValue = $preparedValue[$productId];
                    } else {
                        $sortValue = null;
                    }
                }

                if (!empty($sortValue)) {
                    $fieldName = $this->getSearchEngineFieldName($attribute, 'sort');

                    if ($fieldName) {
                        $productIndexData[$fieldName] = $sortValue;
                    }
                }
            }

            // Adding data for advanced search field (without additional prefix)
            if (($attribute->getIsVisibleInAdvancedSearch() ||  $attribute->getIsFilterable()
                || $attribute->getIsFilterableInSearch())
            ) {
                if ($attribute->usesSource()) {
                    $fieldName = $this->getSearchEngineFieldName($attribute, 'nav');
                    if ($fieldName && !empty($preparedNavValue)) {
                        $productIndexData[$fieldName] = $preparedNavValue;
                    }
                } else {
                    $fieldName = $this->getSearchEngineFieldName($attribute);
                    if ($fieldName && !empty($preparedValue)) {
                        $productIndexData[$fieldName] = in_array($backendType, $this->_textFieldTypes)
                            ? implode(' ', (array)$preparedValue)
                            : $preparedValue ;
                    }
                }
            }

            // Adding data for fulltext search field
            if ($attribute->getIsSearchable() && !empty($preparedValue)) {
                $searchWeight = $attribute->getSearchWeight();
                if ($searchWeight) {
                    $fulltextData[$searchWeight][] = is_array($preparedValue)
                        ? implode(' ', $preparedValue)
                        : $preparedValue;
                }
            }

            unset($preparedNavValue, $preparedValue, $fieldName, $attribute);
        }

        // Preparing fulltext search fields
        $fulltextSpell = array();
        foreach ($fulltextData as $searchWeight => $data) {
            $fieldName = $this->getAdvancedTextFieldName('fulltext', $searchWeight, $storeId);
            $productIndexData[$fieldName] = $this->_implodeIndexData($data);
            $fulltextSpell += $data;
        }
        unset($fulltextData);

        // Preparing field with spell info
        $fulltextSpell = array_unique($fulltextSpell);
        $fieldName = $this->getAdvancedTextFieldName('spell', '', $storeId);
        $productIndexData[$fieldName] = $this->_implodeIndexData($fulltextSpell);
        unset($fulltextSpell);

        // Getting index data for price
        if (isset($this->_indexableAttributeParams['price'])) {
            $priceEntityIndexData = $this->_preparePriceIndexData($productId, $storeId);
            $productIndexData = array_merge($productIndexData, $priceEntityIndexData);
        }

        // Product category index data definition
        $productCategoryIndexData = $this->_prepareProductCategoryIndexData($productId, $storeId);
        $productIndexData = array_merge($productIndexData, $productCategoryIndexData);

        // Define system data for engine internal usage
        $productIndexData['id'] = $productId;
        $productIndexData['store_id'] = $storeId;
        $productIndexData[self::UNIQUE_KEY] = $productId . '|' . $storeId;

        return $productIndexData;
    }
    
    protected function _prepareIndexDefaultData($entityIndexData, $entityId, $storeId, $entityType){
        //process other entity rather than product set default values
        // Define system data for engine internal usage
        $entityIndexData['id'] = $entityId;
        $entityIndexData['store_id'] = $storeId;
        $entityIndexData[self::UNIQUE_KEY] = $entityId . '|' . $storeId. '|' . $entityType;
        $entityIndexData['visibility'] = 4;
        $entityIndexData['in_stock'] = 'false';
        return $entityIndexData;
    }


    public function prepareDocsPerStoreNew($docData, $storeId, $entityType)
    {
        if (!is_array($docData) || empty($docData)) {
            return array();
        }
        $this->_separator = Mage::getResourceSingleton('catalogsearch/fulltext')->getSeparator();
        $docs = array();
        foreach ($docData as $entityId => $entityIndexData) {
            $doc = new $this->_clientDocObjectName;
            $entityIndexData = $this->_prepareIndexDefaultData($entityIndexData, $entityId, $storeId, $entityType);
            if (!$entityIndexData) {
                continue;
            }
            foreach ($entityIndexData as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $val) {
                        if (!is_array($val)) {
                            $doc->addField($name, $val);
                        }
                    }
                } else {
                    $doc->addField($name, $value);
                }
            }
            $docs[] = $doc;
        }
        return $docs;
    }
}
