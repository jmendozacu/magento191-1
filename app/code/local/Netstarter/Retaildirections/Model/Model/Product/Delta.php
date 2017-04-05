<?php

/**
 * Class Netstarter_Retaildirections_Model_Model_Product_Delta
 *
 * Class that imports product changes from the API to the Database.
 * Uses Netstarter_Retaildirections_Model_Client_Connection to handle soap connection.
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 */
class Netstarter_Retaildirections_Model_Model_Product_Delta extends Netstarter_Retaildirections_Model_Model_Product_Abstract
{
    /**
     * Method for the product deltas. It's the same from the "whole catalog" download.
     * However we set a parameter that indicates a timeframe.
     */
    const API_METHOD_PRODUCT_DELTA_LIST = 'BulkItemRangeGet';

    /**
     * This will be filled with a Zend_Date object
     * which can be based on the last execution.
     *
     * @var null|Zend_Date
     */
    protected $_date = null;
    
    /**
     * Website to process in this execution.
     * If null, download all websites configured.
     *
     * @var null|Zend_Date
     */
    protected $_websiteToDownload = null;

    /**
     * @var array
     */
    protected $_productsList = array();
    
    protected $_jobId           = 'PRODUCT_DELTA_DOWNLOAD';
    protected $_logReportMode   = self::LOG_REPORT_MODE_EMAIL;
    protected $_lock            = true;
    
//    public function __construct()
//    {
//        parent::__construct();
//        
//        $this->_logModel->setId($this->_jobId);
//    }

    /**
     * Transforms the Zend_Date object into the actual string to be sent in the API call.
     * The API requests the date to be in the ISO_8601 format.
     *
     * @return string
     */
    protected function _getFormatedDate()
    {
        return $this->_date->get(Zend_Date::ISO_8601);
    }

    /**
     * Does the API call itself to get the product list.
     *
     * This product list is then added to the Netstarter_Retaildirections_Model_Model_Product class
     * which downloads product information for the product list.
     *
     * This list is downloaded once per existing website in the system, as changes are assumed to occur
     * at RD store level. (RD STORE == MAGENTO WEBSITE - e.g. Australia, New Zealand)
     *
     * @param null $websiteCode
     * @return SimpleXMLElement
     */
    protected function _getDeltaMovementList($websiteCode = null)
    {
        if ($websiteCode == null)
        {
            Mage::throwException("Product deltas are per scope. Please provide a website code.");
        }

        if (!array_key_exists($websiteCode, $this->_productsList))
        {
            /*
             * Prepare API call parameters as an array, since it is an simple call.
             */
            $params = array();
            $params['storeCode']    = $this->getStoreId($websiteCode);
            $params['fromDate']     = $this->_getFormatedDate();

            /*
             * Actually does the API call and return the XML.
             */
            $this->_productsList[$websiteCode] = $this->getConnectionModel()->getResult(self::API_METHOD_PRODUCT_DELTA_LIST, $params);
        }

        return $this->_productsList[$websiteCode];
    }
    
    protected function _processProductListPerWebsite($websiteCode = null)
    {
        /*
         * Set the product list into the Netstarter_Retaildirections_Model_Model_Product
         * otherwise it would download all products.
         */

        // Mage::getModel needs to be inside the loop as to destroy all data
        // inside it on each iteration.
        $flatProductModel = Mage::getModel('netstarter_retaildirections/model_product');
        $flatProductModel->setLogModel($this->_logModel);
        
        $error = true;
        $result = $this->_getDeltaMovementList($websiteCode);
        
        if ($result instanceof SimpleXMLElement)
        {
            if (property_exists($result, 'ErrorResponse'))
            {
                $this->_log(array(self::API_METHOD_PRODUCT_DELTA_LIST,
                    'ERROR',
                    $result->ErrorResponse));
            }
            else
            {
                $flatProductModel->setProductsList($result);
                $this->_log(array(self::API_METHOD_PRODUCT_DELTA_LIST,
                        'Product delta listing retrieved with',
                        print_r($flatProductModel->getProductListSize(), true),
                        'product(s) for the',$websiteCode,'website.')
                );

                // actually downloads product data for each scope.
                $flatProductModel->update();

                $error = false;
            }
        }
        else
        {
            $this->_log(array(self::API_METHOD_PRODUCT_DELTA_LIST,
                'ERROR',
                $result->ErrorResponse));
        }
        
        if ($error)
        {
            Mage::throwException("Could not download delta, please check logs/email for more information.");
        }
                
        $this->_log(array(self::API_METHOD_PRODUCT_DELTA_LIST,
                'Finish delta update'));
    }

    /**
     * Calls API for each existing website to retrieve the product list with attribute changes.
     *
     * Class Netstarter_Retaildirections_Model_Model_Product actually downloads
     * the product data for each scope, as the method _getDeltaMovementList only returns the
     * list of products with updated attributes since a specific date.
     */
    protected function _processProductList()
    {
        // Netstarter_Retaildirections_Model_Model_Product
        foreach ($this->_getWebsitesData() as $websiteCode => $isDefault)
        {
            $this->_processProductListPerWebsite($websiteCode);
        }
    }

    /**
     * @param Zend_Date $date
     */
    public function setDate(Zend_Date $date)
    {
        $this->_date = $date;
    }
    

    /**
     * @param Zend_Date $date
     */
    public function setWebsiteToDownload($website)
    {
        $this->_websiteToDownload = trim((string) $website);
    }

    /**
     * Entry point for this class.
     * Does update for updated products from RD since a starting date.
     *
     * @param null $date
     */
    protected function _update($date = null)
    {
        $this->_log(array($this->_jobId, "Starting delta list download"));
        
        if ($date instanceof Zend_Date)
        {
            $this->_date = $date;
        }
        
        if ($this->_websiteToDownload === null)
        {
            $this->_processProductList();
        }
        else
        {
            $isValid = false;
            
            foreach ($this->_getWebsitesData() as $websiteCode => $isDefault)
            {
                if ($this->_websiteToDownload == $websiteCode)
                {
                    $isValid = true;
                }
            }
            
            if ($isValid)
            {
                $this->_processProductListPerWebsite($this->_websiteToDownload);
            }
            else
            {
                $this->_log(array($this->_jobId, "ERROR", "Invalid website code provided:", $this->_websiteToDownload));
            }
        }
        
        $this->_log(array($this->_jobId, "Finishing delta list download"));
    }
    
    public function getSynchronizationCode ()
    {
        if ($this->_websiteToDownload === null)
        {
            return get_class($this . "::_websiteToDownload=null");
        }
        else
        {
            return get_class($this) . "::_websiteToDownload=" . $this->_websiteToDownload;
        }
    }
}