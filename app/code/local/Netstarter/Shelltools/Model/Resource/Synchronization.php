<?php

/**
 * Class Netstarter_Shelltools_Model_Resource_Synchronization
 *
 * @category  Netstarter
 * @package   Netstarter_Shelltools
 *
 */
class Netstarter_Shelltools_Model_Resource_Synchronization extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Set unique fields to be used in Magento default validation.
     */
    protected function _construct()
    {
        $this->_uniqueFields = array(
            array(
            'field' => array('event_code'),
            'title' => 'Combination of event_code should be unique'
            )
        );

        $this->_isPkAutoIncrement = false;
        $this->_init('netstarter_shelltools/synchronization', 'event_code');
    }

    /**
     * Get last successful run for a specific event_code.
     * Next delta (differences) execution will be based on this date.
     *
     * E.g.: if last successful run was 3 days ago then the next execution will try
     * to get the differences since last 3 days ago until today.
     *
     * @param $code string
     * @return string
     */
    public function getLastSuccessRun($code)
    {
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getMainTable(), array($this->getIdFieldName()))
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('last_success_date')
            ->where("event_code = :event_code");

        $bind = array('event_code' => $code);

        $lastRun = $adapter->fetchOne($select, $bind);
        return $lastRun;
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
//    protected function _beforeSave(Mage_Core_Model_Abstract $object)
//    {
//        if ($object->hasDataChanges())
//        {
//            $object->addData(
//                array(
//                    'updated_at' => $this->formatDate(Mage::app()->getLocale()->date(), true)
//                )
//            );
//        }
//        return parent::_beforeSave($object);
//    }
}