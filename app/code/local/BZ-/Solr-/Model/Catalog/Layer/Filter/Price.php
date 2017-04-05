<?php

class BZ_Solr_Model_Catalog_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price
{
    const CACHE_TAG = 'MAXPRICE';
    /**
     * Whether current price interval is divisible
     * @var bool
     */
    protected $_divisible = true;
    /**
     * Ranges faceted data
     * @var array
     */
    protected $_facets = array();
    protected $_is_search = false;
    /**
     * Return cache tag for layered price filter
     * @return string
     */
    public function getCacheTag()
    {
        return self::CACHE_TAG;
    }
    
    /**
     * pre set is search result or layer nav
     */
    public function __construct()
    {
        parent::__construct();
        if(Mage::app()->getRequest()->getModuleName() == 'catalogsearch') $this->_is_search = true;
        else $this->_is_search = false;
    }
    
    public function isSearch(){
        return (bool)$this->_is_search;
    }
    
    /**
     * Get facet field name based on current website and customer group
     * @return string
     */
    protected function _getFilterField()
    {
        $engine = Mage::getResourceSingleton('bz_solr/engine');
        $priceField = $engine->getSearchEngineFieldName('price');

        return $priceField;
    }

    /**
     * Get data for build price filter items
     * @return array
     */
    protected function _getItemsData()
    {
        if (!$this->_divisible) {
            return array();
        }

        $isAuto = (Mage::app()->getStore()
            ->getConfig(self::XML_PATH_RANGE_CALCULATION) == self::RANGE_CALCULATION_IMPROVED);
        if (!$isAuto && $this->getInterval()) {
            return array();
        }

        $facets = $this->getLayer()->getProductCollection()->getFacetedData($this->_getFilterField());
        $data = array();
        if (!empty($facets)) {
            foreach ($facets as $k => $count) {
                if ($count <= 0) {
                    unset($facets[$k]);
                }
            }

            if (!$isAuto && !empty($facets)) {
                $range  = $this->getPriceRange();
            }

            $i = 0;
            $maxIntervalsNumber = $this->getMaxIntervalsNumber();
            $lastSeparator = null;
            foreach ($facets as $key => $count) {
                if (!preg_match('/\[([\d\.\*]+) TO ([\d\.\*]+)\]$/', $key, $separator)) {
                    continue;
                }
                ++$i;

                $label = null;
                $value = null;
                if (isset($this->_facets[$separator[1] . '_' . $separator[2]])) {
                    $separatorLabelValues = $this->_facets[$separator[1] . '_' . $separator[2]];
                    if ($i <= max(1, $maxIntervalsNumber)) {
                        $lastSeparator = $separatorLabelValues[0];
                    }
                    $label = $this->_renderRangeLabel($separatorLabelValues[0], $separatorLabelValues[1]);
                    $value = (empty($separatorLabelValues[0]) ? '' : $separatorLabelValues[0])
                        . '-' . $separatorLabelValues[1];
                }

                if ($isAuto) {
                    if ($separator[1] == '*') {
                        $separator[1] = '';
                    }
                    if ($separator[2] == '*') {
                        $separator[2] = '';
                    }
                } else {
                    $rangeKey = $separator[2] / $range;

                    $rangeKey = round($rangeKey, 2);
                    $separator[1] = ($rangeKey == 1) ? '' : (($rangeKey - 1) * $range);
                    $separator[2] = ($key == null) ? '' : ($rangeKey * $range);
                    // checking max number of intervals
                    if ($i > 1 && $i > $maxIntervalsNumber) {
                        --$i;
                        $count += $data[$i - 1]['count'];
                        $separator[1] = $data[$i - 1]['from'];
                        $label = $value = null;
                    } elseif (!empty($separator[2]) && $separator[2] > $this->getMaxPriceInt()) {
                        $label = $value = null;
                        $separator[2] = '';
                    }
                }

                $data[$i - 1] = array(
                    'label' => is_null($label) ? $this->_renderRangeLabel(
                            empty($separator[1]) ? 0 : ($separator[1] * $this->getCurrencyRate()),
                            empty($separator[2]) ? $separator[2] : $separator[2]  * $this->getCurrencyRate()
                        ) : $label,
                    'value' => (is_null($value) ? ($separator[1] . '-' . $separator[2]) : $value)
                        . $this->_getAdditionalRequestData(),
                    'count' => $count,
                    'from'  => $separator[1],
                    'to'    => $separator[2],
                );
            }

            if (isset($data[$i - 1]) && $data[$i - 1]['from'] != $data[$i - 1]['to']) {
                $upperIntervalLimit = '';
                $appliedInterval = $this->getInterval();
                if ($appliedInterval) {
                    $upperIntervalLimit = $appliedInterval[1];
                }
                if (is_null($value)) {
                    $data[$i - 1]['value'] = $lastSeparator . '-' . $upperIntervalLimit
                        . $this->_getAdditionalRequestData();
                }
                if (is_null($label)) {
                    $data[$i - 1]['label'] = $this->_renderRangeLabel(
                        empty($lastSeparator) ? 0 : $lastSeparator,
                        $upperIntervalLimit
                    );
                }
            }
        }

        return $data;
    }

    /**
     * Get maximum price from layer products set using cache
     * @return float
     */
    public function getMaxPriceInt()
    {
        $searchParams = $this->getLayer()->getProductCollection()->getExtendedSearchParams();
        $uniquePart = strtoupper(md5(serialize($searchParams) . '_' . $this->getCurrencyRate()));
        $cacheKey = 'MAXPRICE_' . $this->getLayer()->getStateKey() . '_' . $uniquePart;

        $cachedData = Mage::app()->loadCache($cacheKey);
        if (!$cachedData) {
            $stats = $this->getLayer()->getProductCollection()->getStats($this->_getFilterField());

            $max = $stats[$this->_getFilterField()]['max'];
            if (!is_numeric($max)) {
                $max = parent::getMaxPriceInt();
            } else {
                $max = floor($max * $this->getCurrencyRate());
            }

            $cachedData = $max;
            $tags = $this->getLayer()->getStateTags();
            $tags[] = self::CACHE_TAG;
            Mage::app()->saveCache($cachedData, $cacheKey, $tags);
        }

        return $cachedData;
    }

    /**
     * Get data with price separators
     * @return array
     */
    protected function _getSeparators()
    {
        $searchParams = $this->getLayer()->getProductCollection()->getExtendedSearchParams();
        $intervalParams = $this->getInterval();
        $intervalParams = $intervalParams ? ($intervalParams[0] . '-' . $intervalParams[1]) : '';
        $uniquePart = strtoupper(md5(serialize($searchParams . '_'
            . $this->getCurrencyRate() . '_' . $intervalParams)));
        $cacheKey = 'PRICE_SEPARATORS_' . $this->getLayer()->getStateKey() . '_' . $uniquePart;

        $cachedData = Mage::app()->loadCache($cacheKey);
        if (!$cachedData) {
            /** @var $algorithmModel Mage_Catalog_Model_Layer_Filter_Price_Algorithm */
            $algorithmModel = Mage::getSingleton('catalog/layer_filter_price_algorithm');
            $statistics = $this->getLayer()->getProductCollection()->getStats($this->_getFilterField());
            $statistics = $statistics[$this->_getFilterField()];

            $appliedInterval = $this->getInterval();
            if (
                $appliedInterval
                && ($statistics['count'] <= $this->getIntervalDivisionLimit()
                || $appliedInterval[0] == $appliedInterval[1]
                || $appliedInterval[1] === '0')
            ) {
                $algorithmModel->setPricesModel($this)->setStatistics(0, 0, 0, 0);
                $this->_divisible = false;
            } else {
                if ($appliedInterval) {
                    $algorithmModel->setLimits($appliedInterval[0], $appliedInterval[1]);
                }
                $algorithmModel->setPricesModel($this)->setStatistics(
                    round($statistics['min'] * $this->getCurrencyRate(), 2),
                    round($statistics['max'] * $this->getCurrencyRate(), 2),
                    $statistics['stddev'] * $this->getCurrencyRate(),
                    $statistics['count']
                );
            }

            $cachedData = array();
            foreach ($algorithmModel->calculateSeparators() as $separator) {
                $cachedData[] = $separator['from'] . '-' . $separator['to'];
            }
            $cachedData = implode(',', $cachedData);

            $tags = $this->getLayer()->getStateTags();
            $tags[] = self::CACHE_TAG;
            Mage::app()->saveCache($cachedData, $cacheKey, $tags);
        }

        if (!$cachedData) {
            return array();
        }

        $cachedData = explode(',', $cachedData);
        foreach ($cachedData as $k => $v) {
            $cachedData[$k] = explode('-', $v);
        }

        return $cachedData;
    }

    /**
     * Prepare faceted value
     * @param float $value
     * @param bool $decrease
     * @return float
     */
    protected function _prepareFacetedValue($value, $decrease = true) {
        // rounding issue
        if ($this->getCurrencyRate() > 1) {
            if ($decrease) {
                $value -= Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
            } else {
                $value += Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
            }
            $value /= $this->getCurrencyRate();
        } else {
            $value /= $this->getCurrencyRate();
            if ($decrease) {
                $value -= Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
            } else {
                $value += Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
            }
        }
        return round($value, 3);
    }

    /**
     * Prepare price range to be added to facet conditions
     * @param string|float $from
     * @param string|float $to
     * @return array
     */
    protected function _prepareFacetRange($from, $to)
    {
        if (empty($from)) {
            $from = '*';
        }
        if ($to === '') {
            $to = '*';
        } else {
            if ($to == $from || ($to == 0 && $from == '*')) {
                $to = $this->_prepareFacetedValue($to, false);
            } else {
                $to = $this->_prepareFacetedValue($to);
            }
        }
        if ($from != '*') {
            $from = $this->_prepareFacetedValue($from);
        }
        //as solr adapter will convert empty string to * if you give them * it will be \* and solr throw errors
        if($from === '*') $from = '';
        if($to === '*') $to = '';
        return array('from' => $from, 'to' => $to);
    }

    /**
     * Add params to faceted search generated by algorithm
     * @return BZ_Solr_Model_Catalog_Layer_Filter_Price
     */
    protected function _addCalculatedFacetCondition()
    {
        $priceFacets = array();
        $this->_facets = array();
        foreach ($this->_getSeparators() as $separator) {
            $facetedRange = $this->_prepareFacetRange($separator[0], $separator[1]);
            $this->_facets[$facetedRange['from'] . '_' . $facetedRange['to']] = $separator;
            $priceFacets[] = $facetedRange;
        }
        $this->getLayer()->getProductCollection()->setFacetCondition($this->_getFilterField(), $priceFacets);
    }

    /**
     * Add params to faceted search
     * @return this
     */
    public function addFacetCondition()
    {
        $calculation = Mage::app()->getStore()->getConfig(self::XML_PATH_RANGE_CALCULATION);
        if ($calculation == self::RANGE_CALCULATION_IMPROVED) {
            return $this->_addCalculatedFacetCondition();
        }

        $this->_facets = array();
        $range    = $this->getPriceRange();
        $maxPrice = $this->getMaxPriceInt();
        $priceFacets = array();
        /** @var $productCollection BZ_Solr_Model_Resource_Collection */
        $productCollection = $this->getLayer()->getProductCollection();
        if ($maxPrice > 0) {
            if ($calculation == self::RANGE_CALCULATION_MANUAL
                || $range == $this->getLayer()->getCurrentCategory()->getFilterPriceRange()
            ) {
                $startPriceInterval = 0;
                $currentMaxPrice = 0;
                $facetCount = $this->getMaxIntervalsNumber();
                do {
                    for ($i = 0; $i < $facetCount; $i++) {
                        $separator = array(
                            $startPriceInterval + $i * $range,
                            $startPriceInterval + ($i + 1) * $range
                        );
                        $facetedRange = $this->_prepareFacetRange($separator[0], $separator[1]);
                        $this->_facets[$facetedRange['from'] . '_' . $facetedRange['to']] = $separator;
                        $priceFacets[] = $facetedRange;
                    }

                    $currentMaxPrice = $priceFacets[count($priceFacets) - 1]['to'];
                    $result = $productCollection->getPriceData($currentMaxPrice, null, 1);
                    $startPriceInterval = count($result) ? floor($result[0] / $range) * $range : $maxPrice;
                    if (($startPriceInterval && --$facetCount == 0) || $startPriceInterval == $maxPrice) {
                        $separator = array(
                            $startPriceInterval,
                            $maxPrice + $range
                        );
                        $facetedRange = $this->_prepareFacetRange($separator[0], $separator[1]);
                        $this->_facets[$facetedRange['from'] . '_' . $facetedRange['to']] = $separator;
                        $priceFacets[] = $facetedRange;
                        break;
                    }
                } while ($maxPrice > $currentMaxPrice);
            } else {
                $facetCount = ceil($maxPrice / $range);
                for ($i = 0; $i < $facetCount + 1; $i++) {
                    $separator = array($i * $range, ($i + 1) * $range);
                    $facetedRange = $this->_prepareFacetRange($separator[0], $separator[1]);
                    $this->_facets[$facetedRange['from'] . '_' . $facetedRange['to']] = $separator;
                    $priceFacets[] = $facetedRange;
                }
            }

            $productCollection->setFacetCondition($this->_getFilterField(), $priceFacets);
        } elseif ($maxPrice == 0) {
            $separator = array(0,1);
            $facetedRange = $this->_prepareFacetRange($separator[0], $separator[1]);
            $this->_facets[$facetedRange['from'] . '_' . $facetedRange['to']] = $separator;
            $priceFacets[] = $facetedRange;
            $productCollection->setFacetCondition($this->_getFilterField(), $priceFacets);
        }

        return $this;
    }

    /**
     * Apply price range filter to collection
     *
     * @return Mage_Catalog_Model_Layer_Filter_Price
     */
    protected function _applyPriceRange()
    {
        list($from, $to) = $this->getInterval();
        $this->getLayer()->getProductCollection()->addFqFilter(array(
            $this->_getFilterField() => $this->_prepareFacetRange($from, $to)
        ));
        //adding values to the min and max
        if(empty($from)) $this->setData('current_min',0);
        else $this->setData('current_min',$from);
        if(empty($to)) $this->setData('current_max',0);
        else $this->setData('current_max',$to);
        return $this;
    }

    /**
     * Get comparing value according to currency rate
     *
     * @param float|null $value
     * @param bool $decrease
     * @return float|null
     */
    protected function _prepareComparingValue($value, $decrease = true)
    {
        if (is_null($value)) {
            return $value;
        }

        if ($decrease) {
            $value -= Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 2;
        } else {
            $value += Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 2;
        }

        $value /= $this->getCurrencyRate();
        if ($value < 0) {
            $value = null;
        }

        return $value;
    }

    /**
     * Load range of product prices
     *
     * @param int $limit
     * @param null|int $offset
     * @param null|int $lowerPrice
     * @param null|int $upperPrice
     * @return array|false
     */
    public function loadPrices($limit, $offset = null, $lowerPrice = null, $upperPrice = null)
    {
        $lowerPrice = $this->_prepareComparingValue($lowerPrice);
        $upperPrice = $this->_prepareComparingValue($upperPrice);
        if (!is_null($upperPrice)) {
            $upperPrice -= Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
        }
        $result = $this->getLayer()->getProductCollection()->getPriceData($lowerPrice, $upperPrice, $limit, $offset);
        if (!$result) {
            return $result;
        }
        foreach ($result as &$v) {
            $v = round((float)$v * $this->getCurrencyRate(), 2);
        }
        return $result;
    }

    /**
     * Load range of product prices, preceding the price
     *
     * @param float $price
     * @param int $index
     * @param null|int $lowerPrice
     * @return array|false
     */
    public function loadPreviousPrices($price, $index, $lowerPrice = null)
    {
        $originLowerPrice = $lowerPrice;
        $lowerPrice = $this->_prepareComparingValue($lowerPrice);
        $price = $this->_prepareComparingValue($price);
        if (!is_null($price)) {
            $price -= Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
        }
        $countLess = $this->getLayer()->getProductCollection()->getPriceData($lowerPrice, $price, null, null, true);
        if (!$countLess) {
            return false;
        }

        return $this->loadPrices($index - $countLess + 1, $countLess - 1, $originLowerPrice);
    }

    /**
     * Load range of product prices, next to the price
     *
     * @param float $price
     * @param int $rightIndex
     * @param null|int $upperPrice
     * @return array|false
     */
    public function loadNextPrices($price, $rightIndex, $upperPrice = null)
    {
        $lowerPrice = $this->_prepareComparingValue($price);
        $price = $this->_prepareComparingValue($price, false);
        $upperPrice = $this->_prepareComparingValue($upperPrice);
        if (!is_null($price)) {
            $price += Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
        }
        if (!is_null($upperPrice)) {
            $upperPrice -= Mage_Catalog_Model_Resource_Layer_Filter_Price::MIN_POSSIBLE_PRICE / 10;
        }
        $countGreater = $this->getLayer()->getProductCollection()->getPriceData($price, $upperPrice, null, null, true);
        if (!$countGreater) {
            return false;
        }

        $result = $this->getLayer()->getProductCollection()->getPriceData(
            $lowerPrice,
            $upperPrice,
            $rightIndex - $countGreater + 1,
            $countGreater - 1,
            false,
            'desc'
        );
        if (!$result) {
            return $result;
        }
        foreach ($result as &$v) {
            $v = round((float)$v * $this->getCurrencyRate(), 2);
        }
        return $result;
    }
    
    /**
     * working for bz_navigation store the old price and curretn filtered price for price slider
     */
    public function getCurrentMaxPrice(){
        if(!is_null($this->getData('current_max'))) return $this->getData('current_max');
        else return 0;
    }
    
    public function getCurrentMinPrice(){
        if(!is_null($this->getData('current_min'))) return $this->getData('current_min');
        else return 0;
    }
    
    protected function _loadOriginalMaxMinPrice(){
        $prices = $this->getLayer()->getProductCollection()->getOriginalPriceStats($this->_getFilterField());
        if(isset($prices[$this->_getFilterField()]['max'])) $this->setData('org_max', ceil($prices[$this->_getFilterField()]['max']+0.01));
        if(isset($prices[$this->_getFilterField()]['min'])) $this->setData('org_min', floor($prices[$this->_getFilterField()]['min']));
    }

    public function getOriginalMaxPirce(){
        if(!is_null($this->getData('org_max'))) return $this->getData('org_max');
        else {
            $this->_loadOriginalMaxMinPrice();
            return $this->getData('org_max');
        }
    }
    
    public function getOriginalMinPirce(){
        if(!is_null($this->getData('org_min'))) return $this->getData('org_min');
        else {
            $this->_loadOriginalMaxMinPrice();
            return $this->getData('org_min');
        }
    }
    
    //always show
    public function getItemsCount(){
        //if selected before then shows
        $params = Mage::app()->getRequest()->getParams();
        $keys = array_keys($params);
        //label not requestVar
        $name = $this->getName();
        $helper = Mage::helper('bz_solr/navigation');
        $clean_name = $helper->labelEncode($name);
        if(in_array($this->getRequestVar(),$keys) || in_array($clean_name,$keys)) return 1;
        else return parent::getItemsCount();
    }
}
