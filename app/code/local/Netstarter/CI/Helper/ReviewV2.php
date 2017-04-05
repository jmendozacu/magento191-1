<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 9/9/13
 * Time: 12:01 PM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_CI_Helper_ReviewV2
{

    protected $_dumpHandle;
    protected $_feedRead;
    protected $_fileBasePath;

    private $_attributes;
    private $_connection;
    private $_entityTable;
    private $_entityTypeId;
    private $_resource;
    private $_attributesToImport;

    private $_ratingResource;
    private $_reviewResource;

    private $_optionsMap = array();
    private $_ratingId = 1;

    protected $_fileName = 'reviews.csv';
    protected $_chunked = false;
    protected $_headers = array(
        'title' => 'ProductTitle',
        'code'  => 'Code',
        'item_code' => 'Item Code',
        'first_name' => 'FirstName',
        'rating' => 'Rating',
        'review' => 'Review'
    );

    public function __construct()
    {
        $this->_fileBasePath = Mage::getBaseDir('var');


        $this->_entityTable   = Mage::getModel('customer/customer')->getResource()->getEntityTable();
        $this->_connection      = Mage::getSingleton('core/resource')->getConnection('write');
        $this->_resource       = Mage::getModel('customer/customer');
        $this->_ratingResource = Mage::getResourceModel('rating/rating_option');
        $this->_reviewResource = Mage::getResourceModel('review/review');

        $this->_rateOptionsLoad();
        $this->_entityTypeId = 1;
    }

    public function setFileName($name) { $this->_fileName = $name; }
    public function getHeaders() { return $this->_headers; }

    protected function _getFeedFilePath()
    {
        return $this->_fileBasePath.DS.'ci'.DS.'feed'.DS.$this->_fileName;
    }


    public function csvToArray($delimiter=',')
    {
        $file = $this->_getFeedFilePath();
        if(!file_exists($file) || !is_readable($file))
            return FALSE;
        $header = array_keys($this->_headers);
        $data = array();
        if (($handle = fopen($file, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 10000, $delimiter)) !== FALSE)
            {

                if(!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }




    protected function _readFeedFile()
    {
        if (file_exists($this->_getFeedFilePath())){

            $this->_feedRead = fopen($this->_getFeedFilePath(), 'r');
        }
    }

    protected function _closeFileHandle()
    {

        if ($this->_dumpHandle !== null) {

            fclose($this->_dumpHandle);
        }

        if ($this->_feedRead !== null) {

            fclose($this->_feedRead);
        }
    }

    protected function _rateOptionsLoad()
    {
        $write = $this->_connection;
        $options = $write->fetchAll("SELECT * FROM  `rating_option` WHERE rating_id = {$this->_ratingId}");

        foreach($options as $option){
            $value = (int) $option['value'];
            $optionId = (int) $option['option_id'];
            $this->_optionsMap[$value] = $optionId;
        }
    }

    public function importReviews()
    {

        echo "Reviews Import Started..... \n";
        $rowDataSet = $this->csvToArray();
        $dateCreated = date('Y-m-d h:i:s');

        $processedItemCodes = array();
        $write = $this->_connection;

        if (!empty($rowDataSet)) {
            foreach ($rowDataSet as $key => $rowData) {

                $entityIds  = array();
                if (isset($processedItemCodes[$rowData['item_code']])) {
                    $entityIds = $processedItemCodes[$rowData['item_code']];

                } else {
                    $entityIds = $write->fetchCol("
                        SELECT
                          p.entity_id
                        FROM
                          `catalog_product_entity` p
                        JOIN
                          catalog_product_entity_varchar v ON p.entity_id = v.entity_id
                        WHERE
                          v.attribute_id =180
                          AND v.value = ?
                        ", $rowData['item_code']);
                    $processedItemCodes[$rowData['item_code']] = $entityIds;
                }

                if (!empty($entityIds)){

                    try{

                        foreach ($entityIds as $key => $entityId) {
                            $write->beginTransaction();
                            $write->query("INSERT INTO review (created_at, entity_id, entity_pk_value, status_id)
                                            VALUES(:created_at, 1, :entity_pk_value, 1)",array('created_at' => $dateCreated,
                                'entity_pk_value'=> $entityId));

                            $reviewId = $write->lastInsertId();

                            $write->query("INSERT INTO review_detail (review_id, store_id, title, detail, nickname)
                                            VALUES(:review_id, 1, :title, :detail, :nickname)",
                                array('review_id' => $reviewId,
                                    'title'=> $rowData['title'],
                                    'detail'=> $rowData['review'],
                                    'nickname'=> "{$rowData['first_name']}"));

                            $write->query("INSERT INTO review_store (review_id, store_id)
                                            VALUES(:review_id, 0), (:review_id, 1), (:review_id, 2)", array('review_id' => $reviewId));

                            if($rowData['rating']){
                                $percent = 20*$rowData['rating'];

                                $write->query("INSERT INTO rating_option_vote (option_id, remote_ip, remote_ip_long, customer_id, entity_pk_value, rating_id, review_id, percent, value)
                                            VALUES(:option_id, '127.0.0.1', 2130706433, NULL, :entity_pk_value, :rating_id, :review_id, :percent, :value)",
                                    array('option_id' => $this->_optionsMap[$rowData['rating']], 'entity_pk_value' => $entityId, 'rating_id'=> $this->_ratingId, 'review_id'=> $reviewId, 'percent'=> $percent, 'value' => $rowData['rating']));

                                $aggregatedProducts[$entityId] = 1;

                            }
                            $write->commit();
                        }


                    }catch (Exception $e){

                        $write->rollback();

                        Mage::log("ERROR in line : $key: {$e->getMessage()}", null, 'review.log');

                        continue;
                    }
                }else{

                    Mage::log("ERROR in line : $key: {$rowData['item_colour_ref']}", null, 'review_product.log');
                }
            }
        }

        echo "Import Finished..... \n";
    }
}