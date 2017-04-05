<?php
/**
 * @author http://www.netstarter.com.au
 * @licence http://www.netstarter.com.au
 */ 
class Netstarter_FitWizard_Model_Combination extends Mage_Core_Model_Abstract
{

    const BACKUP_ROW_STAUS = 'backup';
    protected $_logicTableName;

    protected function _construct()
    {
        $this->_init('fitwizard/combination');
        $this->_logicTableName = Mage::getSingleton('core/resource')->getTableName('fitwizard/fitcategory');
    }


    public function processLogicFile($file)
    {
        $csv = new Varien_File_Csv();
        $data = $csv->getData($file);

        $headers = array_shift($data);

        if(!empty($data)) {

            $this->_prepareBackupData();

            //$collection = $this->getCollection();

            try {
                $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
                $connection->beginTransaction();


                $stepData = Mage::getConfig()->getNode('global/fieldsets/fitwizard_import_fields');
                $stepData = $stepData->asArray();

                foreach ($data as $line) {
                    if (!empty($line)) {
                        $__fields = array();

                        foreach ($stepData as $id => $index) {
                            $__fields[$id] = isset($line[$index]) ? $line[$index] : '';
                        }
                        $__fields['status'] = 'active';
                        $connection->insert($this->_logicTableName, $__fields);
                    }
                }

                $connection->commit();

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }

        } else {
            Mage::throwException('File is empty; No logic was imported');
        }
    }

    protected function _getLogicTableName()
    {
        return $this->_logicTableName = Mage::getSingleton('core/resource')->getTableName('fitwizard/fitcategory');
    }

    protected function _prepareBackupData()
    {

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->beginTransaction();

        //$__condition = array($connection->quoteInto('status =?', self::BACKUP_ROW_STAUS));
        //$connection->delete($this->_logicTableName, $__condition);

        $__fields = array();
        $__fields['status'] = self::BACKUP_ROW_STAUS;
        $__fields['backup_date'] = date('Y-m-d H:i:s');
        $__where = 'status = "active"';
        $connection->update($this->_logicTableName, $__fields, $__where);

        $connection->commit();
    }

    public function clearAllBackupRecords()
    {
        try {
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connection->beginTransaction();

            $__condition = array($connection->quoteInto('status =?', self::BACKUP_ROW_STAUS));
            $connection->delete($this->_logicTableName, $__condition);

            $connection->commit();
            return true;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        return false;
    }

}
