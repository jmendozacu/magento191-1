<?php

/**
 * Class Netstarter_Retaildirections_Model_Model_Product
 *
 * Class that imports products from the API to the Database.
 * Uses Netstarter_Retaildirections_Model_Client_Connection to handle soap connection.
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 */
class Netstarter_Retaildirections_Model_Model_Product extends Netstarter_Retaildirections_Model_Model_Product_Abstract
{
    /**
     * XML path to Magento configuration: how many products per bulk details call?
     */
    const CONFIG_PATH_REFS_PER_CALL = 'netstarter_retaildirections/product/refs_per_call';

    /**
     * Service on the API to list products.
     */
    const API_METHOD_PRODUCT_LIST = 'BulkItemRangeGet';

    /**
     * Service on the API to get product details.
     */
    const API_METHOD_PRODUCT_DETAILS = 'ItemColourDetailsGet';

    /**
     * Service on the API to get product details for the web.
     */
    const API_METHOD_PRODUCT_GROUP_DETAILS = 'GetWebItemDetails';
    
    /**
     * Products list singleton.
     *
     * @var null
     */
    protected $_productsList = null;

    /**
     * Calculated product list size so we don't need to calculate every time.
     *
     * @var null
     */
    protected $_productsListSize = null;

    /**
     * Product details translation from the API properties on the XML to
     * database fields. Used here because we are downloading products in this
     * class.
     *
     * @var array
     */
    protected $_productAttributes = array(
        'itemcolourRef' => 'item_colour_ref',
        'itemCode' => 'item_code',
        'description' => 'description',
        'shortDescription' => 'short_description',
        'itemcolourRef' => 'item_colour_ref',
        'extendedDescription' => 'extended_description',
        'lifecycle' => 'lifecycle',
        'itemfamilygroupCode' => 'item_family_group_code',
        'width' => 'width',
        'depth' => 'depth',
        'height' => 'height',
        'radius' => 'radius',
        'brandName' => 'brand_bame',
        'divisionCode' => 'division_code',
        'baseColourDescription' => 'base_colour_description',
        'baseColourName' => 'base_colour_name',
        'rrp' => 'rrp',
        'currentPrice' => 'current_price',
        'taxAmount' => 'tax_amount',
        'seasonCode' => 'season_code',
        'sellcodeCode' => 'sell_code_code',
        'sizeCode' => 'size_code',
        'quantityAvailable' => 'quantity_available',
        'divisionDescription' => 'division_description',
        'departmentCode' => 'department_code',
        'departmentDescription' => 'department_description',
        'categoryCode' => 'category_code',
        'categoryDescription' => 'category_description',
        'webPageDescription' => 'web_page_description',
        'colourSizeInd' => 'colour_size_ind',
        'itemfamilygroupCode' => 'item_family_group_code',
        'itemfamilygroupDesc' => 'item_family_group_desc',
        'colourDescription' => 'colour_description',
        'overrideColourDesc' => 'override_colour_desc',
        'currencyCode' => 'currency_code',
        'sellable' => 'sellable',
        'webDisplayInd' => 'web_display_ind',
        'shapeTypeInd' => 'shape_type_ind',
        'irregularShapeInd' => 'irregular_shape_ind',
        'sizeDescription' => 'size_description',
        'stockpoolAvailable' => 'stock_pool_available',
        'GoWithItemList' => 'go_with_item_list',
    );

    /**
     * Per store attribues. Needs to have the API called again for the different store.
     *
     * @var array
     */
    protected $_productAttributesSpecific = array (
        'rrp' => 'rrp',
        'currentPrice' => 'current_price',
        'taxAmount' => 'tax_amount',
        'currencyCode' => 'currency_code',
    );
    
    protected $_countForReport                  = array(
        'success'   => 0,
        'error'     => 0,
    );  
    
    protected $_jobId           = 'PRODUCT_ATTRIBUTES_DOWNLOAD';
    protected $_logReportMode   = self::LOG_REPORT_MODE_EMAIL;
    protected $_lock            = true;
    
//    public function __construct()
//    {
//        parent::__construct();
//        
//        $this->_logModel->setId($this->_jobId);
//    }

    /**
     * Returns the attributes specific per store/website scope array.
     * @return array
     */
    public function getProductAttributesSpecific()
    {
        return $this->_productAttributesSpecific;
    }

    /**
     * Flat product model singleton. Saves product data into a flat table.
     * Not to be confused with the Magento product model, it has nothing to do with it.
     *
     * @var $_productModel Netstarter_Retaildirections_Model_Product
     */
    protected $_productModel = null;

    /**
     * Returns Netstarter_Retaildirections_Model_Product singleton
     *
     * @return Netstarter_Retaildirections_Model_Product
     */
    protected function _getFlatProductModel()
    {
        if ($this->_productModel == null)
        {
            $this->_productModel = Mage::getModel('netstarter_retaildirections/product');
        }

        return $this->_productModel;
    }

    /**
     * Retrieves product list from the API.
     *
     * @return SimpleXMLElement
     */
    protected function _getProductList()
    {
        if ($this->_productsList == null)
        {
            /*
             * Prepare API call parameters as an array, since it is an simple call.
             */
            $params = array();
            $params['storeCode'] = $this->getStoreId();

            /*
             * Actually does the API call and return the XML.
             */
            try
            {
                $this->_productsList = $this->getConnectionModel()->getResult(self::API_METHOD_PRODUCT_LIST, $params);
            }
            catch (Exception $e)
            {
                $this->_log(array(self::API_METHOD_PRODUCT_LIST,
                        'Error retrieving product listing',
                        $e->getMessage())
                );
            }
            
            if ($this->_productsList == null)
            {
                
                Mage::throwException("Null product list");
            }
            
            $this->_productsListSize = count($this->_productsList->ItemColourList->ItemColour);

            $this->_log(array(self::API_METHOD_PRODUCT_LIST,
                    'Product listing retrieved with',
                    $this->_getProductListSize(),
                    'product(s)')
            );
        }

        // returns the proper XML node only needed for this business logic.
        return $this->_productsList->ItemColourList;
    }

    /**
     * Retrieves products details from the API
     *
     * @param int $offset
     * @param int $pageSize
     * @return SimpleXMLElement|bool
     */
    protected function _getBulkDetailsSpecificWebsite($offset = 0, $pageSize, $website)
    {
        if (!($pageSize > 0))
        {
            Mage::throwException(
                $this->_getHelper->__("Invalid size for refs per Call (product synchronization).")
            );
        }

        // List size needed to control the flow as we do call for a certain amount
        // of products only per call.
        $totalSize = $this->_getProductListSize();

        // Start building the XML parameter for the call.
        // Always start with the XML_ROOT_NODE.
        $params = new SimpleXMLElement(self::XML_ROOT_NODE);

        $itemColourDetailsGet = $params->addChild("ItemColourDetailsGet");
        $itemColourDetailsGet->addChild('storeCode', $this->getStoreId($website));

        $itemColourList = $params->addChild("ItemColourList");

        for($i = 0; ($i < $pageSize) && (($i+$offset) < $totalSize); $i++)
        {
            $itemColour = $itemColourList->addChild("ItemColour");
            $itemColour->addChild(
                'itemColourRef',
                (string) $this->_getProductList()->ItemColour[$i+$offset]->itemColourRef
            );
        }

        try
        {
            // Performs the actual call to the API.
            $result =  $this->getConnectionModel()->getResult(self::API_METHOD_PRODUCT_DETAILS, $params);
            $resultSize = count($result->ItemColourDetailsList->ItemColourDetails);
        }
        catch (Exception $e)
        {
            $this->_log(array(self::API_METHOD_PRODUCT_DETAILS,
                'Error getting product details listing',
                $e->getMessage()
            ));
        }

        // Depending on the $resultSize we stop retrieving data as per business logic.
        if ($resultSize > 0)
        {
            return $result->ItemColourDetailsList;
        }
        else
        {
            return false;
        }
    }

    /**
     * Get bulk of product data per website.
     *
     * @param int $offset
     * @param $pageSize
     * @return array
     */
    public function _getBulkDetailsMultiWebsite($offset = 0, $pageSize)
    {
        $return = array();

        foreach ($this->_getWebsitesData() as $website => $isDefault)
        {

            $return[$website] = $this->_getBulkDetailsSpecificWebsite($offset, $pageSize, $website);
        }

        return $return;
    }

    /**
     * Retrieves products details from the API.
     *
     * @param string $groupSku
     * @return SimpleXMLElement
     */
    protected function _getWebDetailsSpecificWebsite($groupSku, $website)
    {
        /*
         * Prepare API call parameters as an array, since it is an simple call.
         */
        $params = array();
        $params['itemCode'] = $groupSku;
Mage::log(print_r($website, 1), null, 'website-1.log');        
$params['storeCode'] = $this->getStoreId($website);
        $params['supplyChannelCode'] = $this->getSupplyChannelId($website);

        try
        {
            // Performs the actual call.
            $details = $this->getConnectionModel()->getResult(self::API_METHOD_PRODUCT_GROUP_DETAILS, $params);
            $webitem = $details->WebItem;
        }
        catch (Exception $e)
        {
            $this->_log(array(self::API_METHOD_PRODUCT_GROUP_DETAILS,
                'Error getting web details for',
                $groupSku,
                $e->getMessage())
            );
        }



        return $webitem;
    }

    /**
     * For each sku, get product attributes for each website.
     * Most attributes will be retrieved from the website marked as "default" in the Magento backend.
     *
     * @param $groupSku
     * @return array
     */
    protected function _getWebDetailsMultiWebsite($groupSku)
    {
        $return = array();

        foreach ($this->_getWebsitesData() as $website => $isDefault)
        {
            $return[$website] = $this->_getWebDetailsSpecificWebsite($groupSku, $website);
        }

        return $return;
    }

    /**
     * Count product list size returned by the API.
     *
     * @return int|bool
     */
    protected function _getProductListSize()
    {
        if ($this->_productsListSize == null)
        {
            if ($this->_productsList == null)
            {
                $this->_getProductList();
            }

            if ($this->_productsList != null)
            {
                $this->_productsListSize = count($this->_productsList->ItemColourList->ItemColour);
            }
            else
            {
                Mage::throwException(
                    $this->_getHelper->__("Couldn't measure product list size.")
                );

                return false;
            }
        }

        return $this->_productsListSize;
    }

    /**
     * Public version to count product list size returned by the API.
     *
     * @return int|bool
     */
    public function getProductListSize()
    {
        return $this->_getProductListSize();
    }

    /**
     * Turns XML properties into database fields.
     *
     * @param SimpleXMLElement $data
     * @return array
     */
    protected function _prepareData (SimpleXMLElement $data)
    {
        $preparedAttributes = array();
        foreach ($data->children() as $k => $v)
        {
            if (array_key_exists($k, $this->_productAttributes))
            {
                $preparedAttributes[$this->_productAttributes[$k]] = (string) $v;
            }
        }
        return $preparedAttributes;
    }

    /**
     * Filter result retrieved by API call into scoped fields for multi-website values.
     *
     * e.g. turn rrp into newzealand_rrp
     *
     * @param array $resultXML
     * @param array $webDetailsXML
     * @param SimpleXMLElement $defaultProductXML
     * @return array
     */
    protected function _prepareSpecificWebsiteData(array $resultXML,
                                                   array $webDetailsXML,
                                                   SimpleXMLElement $defaultProductXML)
    {
        $preparedAttributes = array();

        $groupSku = (string) $defaultProductXML->itemCode;
        $colourSku = (string) $defaultProductXML->itemcolourRef;

        foreach ($this->_getWebsitesData() as $website => $isDefault)
        {
            if ($isDefault)
            {
                continue;
            }
            
            // merging priority is 1) API_METHOD_PRODUCT_GROUP_DETAILS 2) API_METHOD_PRODUCT_DETAILS
            // this is because API_METHOD_PRODUCT_DETAILS is more important as it contains colour level data (BNT-766)
            
            // for 2nd api call (API_METHOD_PRODUCT_GROUP_DETAILS)
            foreach($webDetailsXML[$website]->WebItemColourList->children() as $webColourDetails)
            {
                if ((string) $webColourDetails->itemColourReference != $colourSku)
                {
                    continue;
                }

                foreach ($webColourDetails->children() as $k => $v)
                {
                    if (array_key_exists($k, $this->_productAttributesSpecific))
                    {
                        $preparedAttributes[$website.'_'.$this->_productAttributesSpecific[$k]] = (string) $v;
                    }
                }
            }

            // for 1st api call (API_METHOD_PRODUCT_DETAILS)
            foreach ($resultXML[$website]->children() as $specificProductXML)
            {
                $xmlSkuItem = (string) $specificProductXML->itemCode;
                $xmlSkuColour = (string) $specificProductXML->itemcolourRef;

                if ($groupSku != $xmlSkuItem)
                {
                    continue;
                }
                
                if ($colourSku != $xmlSkuColour)
                {
                    continue;
                }

                foreach ($specificProductXML->children() as $k => $v)
                {            
                    if (array_key_exists($k, $this->_productAttributesSpecific))
                    {
                        $preparedAttributes[$website.'_'.$this->_productAttributesSpecific[$k]] = (string) $v;
                    }
                }
            }

        }

        return $preparedAttributes;
    }

    /**
     * Process XML for each product.
     *
     * @param SimpleXMLElement $defaultProductXML
     * @param SimpleXMLElement $defaultWebDetailsXML
     * @return array
     */
    protected function _processProductXML ( SimpleXMLElement $defaultProductXML,
                                            SimpleXMLElement $defaultWebDetailsXML,
                                            array $resultXML,
                                            array $webDetailsXML)
    {
        // replicable data
        $webColourDataProcessed = array();

        foreach($defaultWebDetailsXML->WebItemColourList->children() as $webColourDetails)
        {
            if ((string) $webColourDetails->itemColourReference == (string) $defaultProductXML->itemcolourRef)
            {
                $webColourDataXML = $webColourDetails;
                $webColourDataProcessed = $this->_prepareData($webColourDetails);
            }
        }

        $specificData = $this->_prepareSpecificWebsiteData($resultXML, $webDetailsXML, $defaultProductXML);

        // merging priority is 1) API_METHOD_PRODUCT_GROUP_DETAILS 2) API_METHOD_PRODUCT_DETAILS
        // this is because API_METHOD_PRODUCT_DETAILS is more important as it contains colour level data (BNT-766)
        $productColourData = array_merge(
            $this->_prepareData($defaultWebDetailsXML),
            $webColourDataProcessed,
            $this->_prepareData($defaultProductXML),
            $specificData,
            array('retrieved_from_api_at' => $this->_getFlatProductModel()
                    ->getResource()->formatDate(Mage::app()->getLocale()->date(), true))
        );

        $products = array();

        // product doesn't have sizes
        if (!isset($defaultProductXML->quantitiesAvailable))
        {
            return array($productColourData);
        }

        // specific data per size
        foreach($defaultProductXML->quantitiesAvailable->children() as $size)
        {
            $sizeWebData = array();
            $sizeData = $this->_prepareData($size);

            foreach($webColourDataXML->WebItemColourSizeList->children() as $webSize)
            {
                if ((string) $size->sizeCode == (string) $webSize->sizeCode)
                {
                    $sizeWebData = $this->_prepareData($webSize);
                }
            }

            $products[] = array_merge($productColourData, $sizeData, $sizeWebData);
        }

        // all skus
        return $products;
    }

    /**
     * Gets data from the API and turns it into flat database table.
     */
    protected function _processList()
    {
        /*
         * First API call, bring all products.
         */
        $this->_getProductList();

        /*
         * Information for flow control.
         * We get product details per specified chunck size to minimize memory usage.
         */
        $totalSize = $this->_getProductListSize();
        $offset = 0; //$this->_getProductListSize() - 50;
        $pageSize = Mage::getStoreConfig(self::CONFIG_PATH_REFS_PER_CALL);

//        $this->_log(array($totalSize, $offset, $pageSize));

        /*
         * Second API call: details for a chunk of the products.
         */
        while ($offset < $totalSize)
        {
            $resultXML = $this->_getBulkDetailsMultiWebsite($offset, $pageSize);
            if (!is_object($resultXML[$this->_getDefaultWebsiteCode()]))
            {
                Mage::throwException("Wrong request/response size calculation. Totalsize: $totalSize, Offset: $offset, Pagesize: $pageSize");
            }
            
            // Looping the default website as a way to get through the products
            foreach($resultXML[$this->_getDefaultWebsiteCode()]->children() as $defaultProductXML)
            {
                try
                {
                    $groupSku = (string) $defaultProductXML->itemCode;
                    $colourSku = (string) $defaultProductXML->itemcolourRef;

                    /*
                     * Third API call: web details per product.
                     */
Mage::log(print_r($colourSku, 1), null, 'coloursku.log');                    
$webDetailsXML = $this->_getWebDetailsMultiWebsite($groupSku);
Mage::log(print_r($webDetailsXML, 1), null, 'website-details.log');
                    $defaultWebDetailsXML = $webDetailsXML[$this->_getDefaultWebsiteCode()];
//$defaultWebDetailsXML = $webDetailsXML['base'];
//print_r('base');
 if(!is_null($defaultWebDetailsXML)) {
Mage::log(print_r($this->_getDefaultWebsiteCode(), 1), null, 'website.log');            
        // Process API returned data and save it to database.
                    $rows = $this->_processProductXML($defaultProductXML, $defaultWebDetailsXML, $resultXML, $webDetailsXML);
                    Mage::log(print_r($rows, 1), null, 'product_inventory.log');
                    // Each size is a row in the database.
                    // Configurable main data is duplicated on purpose.
                    foreach($rows as $row)
                    {
                        $id = $this->_getFlatProductModel()->getIdByKeys($row);
                        $this->_getFlatProductModel()->clearInstance();

                        // Handle updates.
                        if ($id > 0)
                        {
                            $this->_getFlatProductModel()
                                ->load($id);
                        }

                        // Add data and save it to the database.
                        $this->_getFlatProductModel()
                            ->addData($row);

                        $this->_getFlatProductModel()->save();
                    }
                    
                    $this->_countForReport['success']++;
                    $this->_log(array("Product",$colourSku,"downloaded successfully."));
		}			
		else
                    	{
                        $this->_countForReport['error']++;
                        $this->_log(array("Product", $colourSku, "had an ERROR Because of GetWebItemDetails return null."));
                    }
                }
                catch (Exception $e)
                {
                    $this->_countForReport['error']++;
                    $this->_log(array("Product",$colourSku,"had an ERROR.",$e->getMessage()));
                }
            }

            $offset += $pageSize;
        }
    }

    /**
     * Entry point for product information download.
     */
    protected function _update($date = null)
    {
        $this->_log(array($this->_jobId, "Starting process of importing product data"));
            
        try
        {
            $this->_processList();
        }
        catch (Exception $e)
        {
            $this->_log('ERROR:',$e->getMessage());
        }
        
        $this->_log(array(
            $this->_jobId,
            "Finishing process of importing product data with successes:",
            $this->_countForReport['success'],
            "and errors:",
            $this->_countForReport['error']
        ));
    }

    /**
     * @param $list
     * @return $this
     */
    public function setProductsList($list)
    {
        $this->_productsList = $list;
        $this->_productsListSize = null;
        
        return $this;
    }
}
