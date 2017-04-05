<?php

/**
 * Class Netstarter_Retaildirections_Model_Model_Product_Inventory
 *
 * Class that imports inventory from the API to the Database.
 * Uses Netstarter_Retaildirections_Model_Client_Connection to handle soap connection.
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 */
class Netstarter_Retaildirections_Model_Model_Product_Inventory extends Netstarter_Retaildirections_Model_Model_Product_Abstract
{
    /**
     * Method on the API to retrieve stock Deltas.
     * We only call for one store, as inventory is the same for all websites.
     */
    const API_METHOD_PRODUCT_INVENTORY = 'ItemColourStockMovementFind';

    /**
     * This will be filled with a Zend_Date object
     * which can be based on the last execution.
     *
     * @var null|Zend_Date
     */
    protected $_date = null;

    /**
     * @var null
     */
    protected $_productsList = null;
    
    protected $_jobId           = 'PRODUCT_INVENTORY_DOWNLOAD';
    protected $_logReportMode   = self::LOG_REPORT_MODE_EMAIL;
    protected $_lock            = true;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_logModel->setId($this->_jobId);
    }

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
     * Does the API call to retrieve the product inventory changes.
     *
     * This product list is then added to the Netstarter_Retaildirections_Model_Model_Product class
     * which downloads product information for the product list.
     *
     * Inventory is assumed to be the same for all websites/stores.
     * (RD STORE == MAGENTO WEBSITE - e.g. Australia, New Zealand)
     *
     * @return SimpleXMLElement
     */
    protected function _getStockMovementList()
    {
        if ($this->_productsList == null)
        {
            /*
             * Prepare API call parameters as an array, since it is an simple call.
             */
            $params = array();
            $params['storeCode']    = $this->getStoreId();
            $params['fromDateTime'] = $this->_getFormatedDate();

            /*
             * Actually does the API call and return the XML.
             */
            $this->_productsList = $this->getConnectionModel()->getResult(self::API_METHOD_PRODUCT_INVENTORY, $params);
        }

        return $this->_productsList;
    }

    /**
     * Calls API for each existing website to retrieve the product list with inventory changes.
     *
     * Class Netstarter_Retaildirections_Model_Model_Product actually downloads
     * the product data for each scope, as the method _getStockMovementList only returns the
     * list of products with updated stock since a specific date.
     */
    protected function _processProductList()
    {
        $flatProductModel = Mage::getModel('netstarter_retaildirections/model_product');
        $flatProductModel->setLogModel($this->_logModel);
        
        $error = true;
        $result = $this->_getStockMovementList();
        if ($result instanceof SimpleXMLElement)
        {
            if (property_exists($result, 'ErrorResponse'))
            {
                $this->_log(array(self::API_METHOD_PRODUCT_INVENTORY,
                    'ERROR',
                    $result->ErrorResponse));
            }
            else
            {
                $flatProductModel->setProductsList($result);
$this->_logReportMode='log';
                $this->_log(array(self::API_METHOD_PRODUCT_INVENTORY,
                        'Products with inventory changes listing retrieved with',
                        print_r($flatProductModel->getProductListSize(), true),
                        'product(s)')
                );

                // actually downloads product data for each scope, including stock levels.
                $flatProductModel->update();
                
                $error = false;
            }
        }
        else
        {
            $this->_log(array(self::API_METHOD_PRODUCT_INVENTORY,
                'ERROR',
                $result->ErrorResponse));
        }
        
        if ($error)
        {
            Mage::throwException("Could not download inventory, please check logs/email for more information.");
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
     * Entry point for this class.
     * Does update for products with stock changed in RD since a starting date.
     *
     * @param null $date
     */
    protected function _update($date = null)
    {
        $this->_log(array($this->_jobId, "Starting inventory update list download"));
        
        if ($date instanceof Zend_Date)
        {
            $this->_date = $date;
        }

        $this->_processProductList();
        
        $this->_log(array($this->_jobId, "Finishing inventory update list download"));
    }
}
