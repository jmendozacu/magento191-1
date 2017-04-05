<?php

/**
 * Exact target CSV generation file
 *
 * @category  Netstarter
 * @package   Netstarter_Exacttarget
 *
 * Class Netstarter_Exacttarget_Model_Observer
 */
class Netstarter_Exacttarget_Model_Observer extends Netstarter_Shelltools_Model_Shared_Abstract
{
    // config path list
    const CONFIG_XML_PATH_URL       = 'netstarter_exacttarget/connection/url_production';
    const CONFIG_XML_PATH_USER      = 'netstarter_exacttarget/connection/username_production';
    const CONFIG_XML_PATH_PASSWORD  = 'netstarter_exacttarget/connection/password_production';
    const CONFIG_XML_PATH_PATH      = 'netstarter_exacttarget/connection/path';
    const CONFIG_XML_PATH_FILENAME              = 'netstarter_exacttarget/connection/filename';
    const CONFIG_XML_PATH_ORDER_FILENAME        = 'netstarter_exacttarget/connection/order_filename';
    const CONFIG_XML_PATH_ORDER_ITEM_FILENAME   = 'netstarter_exacttarget/connection/orderitem_filename';

    const TYPE_SALES_ORDER = 'sales_order';
    const TYPE_SALES_ORDER_ITEM = 'sales_order_item';
    const TYPE_SUBSCRIBER = 'subscriber';


    protected $_jobId           = 'EXACTTARGET_CSV_GENERATION';
    protected $_orderIds        = array();
    protected $_orders          = array();
    /*
     * CSV header
     */
    public function getHeaderArray()
    {
        return array(
            "SubscriberKey",
            "CustomerId",
            "EmailAddress",
            "Title",
            "FirstName",
            "LastName",
            "DateOfBirth",
            "Status",
            "Website"
        );
    }

    /*
     * return | array
     * Header fields for ExactTarget sales order export
     */
    public function getSalesOrderHeaderArray() {
        return array(
            "CustomerKey",
            "OrderNumber",
            "OrderValue",
            "PurchaseDate",
            "NumberOfItems",
        );
    }

    /*
     * return | array
     * Header fields for ExactTarget sales order ITEM export
     */
    public function getSalesOrderItemHeaderArray() {
        return array(
            "OrderNumber",
            "ProductCode",
            "Quantity",
            "Price",
        );
    }


    /*
     * SFTP Url admin config.
     */
    protected function _getUrl()
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_URL);
    }
    
    /*
     * SFTP username admin config.
     */
    protected function _getUsername()
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_USER);
    }
    
    /*
     * SFTP pwd admin config.
     */
    protected function _getPassword()
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_PASSWORD);
    }
    
    /*
     * SFTP path inside the server the file will be left
     */
    protected function _getPath()
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_PATH);
    }

    /**
     * Getters setters
     */
    protected function setOrderIds ($ids = array()) {$this->_orderIds = $ids;  }
    protected function getOrderIds () {return $this->_orderIds; }

    protected function setOrders ($orders = array()) {$this->_orders = $orders;  }
    protected function getOrders () {return $this->_orders; }
    /*
     * Generate final filename.
     * Can have a timestamp or not.
     * 
     * @param string $current
     */
    protected function _processFinalFilename($current = null, $type=null)
    {
        $backendFilename = trim(Mage::getStoreConfig(self::CONFIG_XML_PATH_FILENAME));

        switch($type) {
            case self::TYPE_SALES_ORDER:
                $backendFilename = trim(Mage::getStoreConfig(self::CONFIG_XML_PATH_ORDER_FILENAME));
                break;
            case self::TYPE_SALES_ORDER_ITEM:
                $backendFilename = trim(Mage::getStoreConfig(self::CONFIG_XML_PATH_ORDER_ITEM_FILENAME));
                break;
            default:
                break;
        }


        if ( strlen($backendFilename) > 0 )
        {
            return $backendFilename;
        }

        return $current;
    }
    
    protected function _generateCsv()
    {
        try
        {
            $this->_log(array("Starting Customer CSV generation."));
            
            // get subscriber collections
            $subscriber     = Mage::getModel('newsletter/subscriber')->getCollection();
            
            $adapter        = $subscriber->getConnection();
            $customer       = Mage::getModel('customer/customer');
            $websiteTable   = Mage::getModel('core/website')->getResource()->getMainTable();


            // Add customer Data
            $firstname  = $customer->getAttribute('firstname');
            $lastname   = $customer->getAttribute('lastname');
            $dob        = $customer->getAttribute('dob');
            $prefix     = $customer->getAttribute('prefix');
            $websiteId  = $customer->getAttribute('website_id');

            $subscriber->getSelect()
               ->joinLeft(
                    array('customer_prefix_table'=>$prefix->getBackend()->getTable()),
                    $adapter->quoteInto('customer_prefix_table.entity_id=main_table.customer_id
                     AND customer_prefix_table.attribute_id = ?', (int)$prefix->getAttributeId()),
                    array('customer_prefix'=>'value')
                )
                ->joinLeft(
                    array('customer_lastname_table'=>$lastname->getBackend()->getTable()),
                    $adapter->quoteInto('customer_lastname_table.entity_id=main_table.customer_id
                     AND customer_lastname_table.attribute_id = ?', (int)$lastname->getAttributeId()),
                    array('customer_lastname'=>'value')
                )
                ->joinLeft(
                    array('customer_firstname_table'=>$firstname->getBackend()->getTable()),
                    $adapter->quoteInto('customer_firstname_table.entity_id=main_table.customer_id
                     AND customer_firstname_table.attribute_id = ?', (int)$firstname->getAttributeId()),
                    array('customer_firstname'=>'value')
                )
                ->joinLeft(
                    array('customer_dob_table'=>$dob->getBackend()->getTable()),
                    $adapter->quoteInto('customer_dob_table.entity_id=main_table.customer_id
                     AND customer_dob_table.attribute_id = ?', (int)$dob->getAttributeId()),
                    array('customer_dob'=>'value')
                )
                ->joinLeft(
                    array('customer_websiteid_table'=>$websiteId->getBackend()->getTable()),
                    'customer_websiteid_table.entity_id=main_table.customer_id',
                    array()
                )->joinLeft(
                    array('website_name'=>$websiteTable),
                    'customer_websiteid_table.website_id=website_name.website_id',
                    array('website_name'=>'name')
                );

            
            // write to disk
            $io = new Varien_Io_File();

            $path = Mage::getBaseDir('var') . DS . 'export' . DS;
            $filename = 'customer_exacttarget_'.date('Y_m_d_H_i_s').'.csv';

            $io->setAllowCreateFolders(true);
            $io->open(array('path' => $path));
            $io->streamOpen($filename, 'w+');
            $io->streamLock(true);

            $io->streamWriteCsv(
                $this->getHeaderArray()
            );
            
            $this->_log(array("Generating file with " . count($subscriber) . " subscribers"));
            

            foreach ($subscriber as $c)
            {
                $createDate = new DateTime($c->getCustomerDob());
                $dateOnly = $createDate->format('Y-m-d');

                $subscribed = $c->getSubscriberStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED ?
                        "Active" :
                        "Unsubscribe";
                
                $customerID = strval($c->getCustomerId()) !== "0" ? $c->getCustomerId() : "";

                $io->streamWriteCsv(
                    array(
                        $c->getSubscriberId(),
                        $customerID,
                        $c->getSubscriberEmail(),
                        $c->getCustomerPrefix(),
                        $c->getCustomerFirstname(),
                        $c->getCustomerLastname(),
                        $dateOnly,
                        $subscribed,
                        $c->getWebsiteName(),
                    )
                );
            }

            $io->streamUnlock();
            $io->streamClose();


            // write to sftp
            $serverFilename = $this->_uploadFile($path, $filename);
            
            $this->_log(array("Finished $serverFilename upload."));
        }
        catch (Exception $e)
        {
            $this->_log(array("ERROR", $e->getMessage()), Zend_Log::ERR);
        }
    }
    
    /*
     * Gather data from DB and generate CSV
     */
    protected function _update()
    {
        $this->_generateCsv();
        $this->_generateOrderCsv();
        $this->_generateOrderItemCsv();
    }
    
    /*
     * Uploads file to SFTP
     * 
     * @param string $localPath
     * @param string $localFilename
     */
    protected function _uploadFile($localPath, $localFilename, $type=null)
    {
        // write to sftp
        $serverFilename = $this->_processFinalFilename($localFilename, $type);
        $host = $this->_getUrl();
        
        $this->_log(array("Opening connection to host: $host"));
        
        $connection = Mage::getModel('netstarter_exacttarget/io_sftp');
        $connection->open(array(
            'host'     => $host,
            'username' => $this->_getUsername(),
            'password' => Mage::helper('core')->decrypt($this->_getPassword())
        ));
        
        $connection->cd($this->_getPath());
        $connection->rm($serverFilename);

        $connection->write($serverFilename, $localPath.$localFilename, NET_SFTP_LOCAL_FILE);
        $connection->close();
        
        return $serverFilename;
    }

    protected function _generateOrderCsv() {
        try
        {
            $this->_log(array("Starting Order CSV generation."));
            $extractFromTime = strtotime('-1 day', time());
            $extractToTime = time();

            $this->_log(array("Orders placed From: ".date('Y-m-d h:i:s', $extractFromTime) ." to ". date('Y-m-d h:i:s', $extractToTime)));

            $orders = Mage::getModel('sales/order')->getCollection()
                ->addFieldToSelect('entity_id')
                ->addFieldToSelect('customer_id')
                ->addFieldToSelect('increment_id')
                ->addFieldToSelect('grand_total')
                ->addFieldToSelect('created_at')
                ->addFieldToSelect('total_qty_ordered')
                ->addFieldToFilter('created_at', array(
                    'from'     => $extractFromTime,
                    'to'       => $extractToTime,
                    'datetime' => true
                ))
                ->addFieldToFilter('customer_is_guest', 0)
                ->setOrder('entity_id', Varien_Data_Collection::SORT_ORDER_ASC)
            ;

            // write to disk
            $io = new Varien_Io_File();

            $path = Mage::getBaseDir('var') . DS . 'export' . DS;
            $filename = 'order_exacttarget_'.date('Y_m_d_H_i_s').'.csv';

            $io->setAllowCreateFolders(true);
            $io->open(array('path' => $path));
            $io->streamOpen($filename, 'w+');
            $io->streamLock(true);

            $io->streamWriteCsv(
                $this->getSalesOrderHeaderArray()
            );

            $this->_log(array("Generating file with " . count($orders) . " Orders placed within last 24hrs"));

            $fetchedOrderIds = array();
            foreach ($orders as $c)
            {
                $fetchedOrderIds[] = $c->getEntityId();
                $io->streamWriteCsv(
                    array(
                        $c->getData('customer_id'),
                        $c->getData('increment_id'),
                        round($c->getData('grand_total'), 2),
                        $c->getData('created_at'),
                        intVal($c->getData('total_qty_ordered')),
                    )
                );
            }

            $this->setOrderIds($fetchedOrderIds);
            $this->setOrders($orders);

            $io->streamUnlock();
            $io->streamClose();

            // write to sftp
            $serverFilename = $this->_uploadFile($path, $filename, self::TYPE_SALES_ORDER);

            $this->_log(array("Finished $serverFilename upload."));
        }
        catch (Exception $e)
        {
            $this->_log(array("ERROR", $e->getMessage()), Zend_Log::ERR);
        }
    }

    protected function _generateOrderItemCsv() {
        $orders = $this->getOrders();
        try
        {
            if ($orders) {

                $this->_log(array("Order items for the orders placed within last 24hrs. Refer Sales Order Report"));

                // write to disk
                $io = new Varien_Io_File();

                $path = Mage::getBaseDir('var') . DS . 'export' . DS;
                $filename = 'orderitem_exacttarget_'.date('Y_m_d_H_i_s').'.csv';

                $io->setAllowCreateFolders(true);
                $io->open(array('path' => $path));
                $io->streamOpen($filename, 'w+');
                $io->streamLock(true);

                $io->streamWriteCsv(
                    $this->getSalesOrderItemHeaderArray()
                );

                $this->_log(array("Generating file with Orders placed within last 24hrs"));

                foreach ($orders as $order)
                {
                    $items = $order->getAllVisibleItems();

                    if (count($items)) {
                        foreach ($items as $item) {
                            $io->streamWriteCsv(
                                array(
                                    $order->getData('increment_id'),
                                    $item->getData('sku'),
                                    intVal($item->getData('qty_ordered')),
                                    round($item->getData('row_total_incl_tax'), 2)
                                )
                            );
                        }
                    }

                }

                $io->streamUnlock();
                $io->streamClose();

                // write to sftp
                $serverFilename = $this->_uploadFile($path, $filename, self::TYPE_SALES_ORDER);

                $this->_log(array("Finished $serverFilename upload."));
            } else {
                $this->_log("No order Ids found ");
            }
        }
        catch (Exception $e)
        {
            $this->_log(array("ERROR", $e->getMessage()), Zend_Log::ERR);
        }
    }
}