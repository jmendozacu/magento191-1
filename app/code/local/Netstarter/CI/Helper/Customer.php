<?php
ini_set('memory_limit','8000M');
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 9/9/13
 * Time: 12:01 PM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_CI_Helper_Customer
{

    protected $_dumpHandle;
    protected $_feedRead;
    protected $_fileBasePath;
    protected $_header;


    public function __construct()
    {
        $this->_fileBasePath = Mage::getBaseDir('var');
    }

    protected function _getFeedFilePath()
    {
        return $this->_fileBasePath.DS.'ci'.DS.'feed'.DS.'customers.csv';
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

    private function _prepareHeaders($header)
    {
        $headerArray = explode(',' , $header);

        foreach($headerArray as $col){

            $this->_header[] = $col;
        }
    }

    private function _prepareRow($line)
    {
        $bunch = explode(',' , $line);
        if(count($bunch) == 60){

            $header = $this->_header;

            $rowData = array_combine($header, $bunch);

            return $rowData;
        }

        return null;
    }

    public function importCustomer()
    {
        $this->_readFeedFile();

        if ($this->_feedRead !== null) {

            $header = fgets($this->_feedRead);

            $this->_prepareHeaders($header);

            $adapter= new Mage_Customer_Model_Convert_Adapter_Customer();

            while (!feof($this->_feedRead)) {

                try{

                    $line = fgets($this->_feedRead);
                    if(empty($line)) continue;

                    if($rowData = $this->_prepareRow($line)){

                        echo "{$rowData['email']}\n";

                        $adapter->saveRow($rowData);

                        unset($rowData);
                    }

                }catch (Exception $e){

                    mage::log($e->getMessage(), null, 'ci_customer_import.log');

                    continue;
                }
            }
        }

        $this->_closeFileHandle();
    }
}