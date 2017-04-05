<?php
ini_set('memory_limit','8000M');
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 9/9/13
 * Time: 12:01 PM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_CI_Helper_Password
{

    protected $_dumpHandle;
    protected $_feedRead;
    protected $_fileBasePath;
    protected $_header;
    public $_updateFile;
    public $_connection;


    public function __construct()
    {
        $this->_fileBasePath = Mage::getBaseDir('var');
        $this->_connection      = Mage::getSingleton('core/resource')->getConnection('write');
        $this->_updateFile = 'pwd_upd' . $importFile = Mage::getModel('core/date')->date('Ymd') . '.sql';
    }

    protected function _getFeedFilePath()
    {
        return $this->_fileBasePath.DS.'ci'.DS.'feed'.DS.'customers_password.csv';
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

    private function _prepareRow($line)
    {
        $bunch = explode(',' , $line);
        if(count($bunch) == 3){


            return $bunch;
        }

        return null;
    }

    public function importCustomer()
    {
        $this->_readFeedFile();

        if ($this->_feedRead !== null) {

            $eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
            $passwordAttrId = (int) $eavAttribute->getIdByCode('customer', 'password_hash');
            $coreHelper = Mage::helper('core');

            fgets($this->_feedRead);

            while (!feof($this->_feedRead)) {

                try{

                    $line = fgets($this->_feedRead);
                    if(empty($line)) continue;

                    if($rowData = $this->_prepareRow($line)){

                        if(!empty($rowData[0]) && !empty($rowData[1])){

                            echo "{$rowData[0]}\n";

                            $resultCheck = $this->_connection->query("SELECT entity_id FROM `customer_entity` c WHERE email = '{$rowData[0]}'");

                            $passWord = $coreHelper->getHash($rowData[1], 2);

                            if ($resultCheck !== false &&  $customer = (int) $resultCheck->fetchColumn()){

                                $passWordCheck = $this->_connection->query("SELECT value_id, value FROM `customer_entity_varchar` c WHERE entity_id = $customer AND attribute_id = $passwordAttrId AND entity_type_id = 1");

                                if ($passWordCheck !== false &&  $val = $passWordCheck->fetch(Zend_Db::FETCH_ASSOC)){

                                    if(!empty($val['value_id']) && empty($val['value'])){

                                        $valId = $val['value_id'];
                                        exec("echo \"UPDATE customer_entity_varchar SET value = '{$passWord}' WHERE value_id = $valId;\" >> " . Mage::getBaseDir().'/var/ci/unprocessed/'. $this->_updateFile);
                                        mage::log($rowData[0], null, 'ci_pwd_import_emails.log');
                                    }
//
                                }else{
                                    exec("echo \"INSERT INTO customer_entity_varchar (entity_type_id, attribute_id, entity_id, value) VALUES (1, $passwordAttrId, $customer,'{$passWord}');\" >> " . Mage::getBaseDir().'/var/ci/unprocessed/'. $this->_updateFile);
                                }
                            }
                        }

                        unset($rowData);
                    }

                }catch (Exception $e){

                    mage::log($e->getMessage(), null, 'ci_pwd_import.log');

                    continue;
                }
            }
        }

        $this->_closeFileHandle();
    }
}