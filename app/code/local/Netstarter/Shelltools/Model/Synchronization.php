<?php

/**
 * Class Netstarter_Shelltools_Model_Synchronization
 *
 * @category  Netstarter
 * @package   Netstarter_Shelltools
 *
 * Helps synchronize API calls, as this class retains dates from last call
 * and last successful calls. It also automatically generates the Zend_Date objcet
 * on which the next call will be based into and automatically logs current date
 * to be used on the next API call.
 *
 */
class Netstarter_Shelltools_Model_Synchronization extends Mage_Core_Model_Abstract
{
    /**
     * Keyword to be used as parameter to retrieve a specific date when
     * this API call last successful ran.
     */
    const LAST_SUCCESS_KEYORD = 'last_success';

    /**
     * Default Zend_Date date retrieval, in case
     * no parameter can be filtered from the cron/shell command.
     */
    const DEFAULT_TIMEFRAME_HOURS = '36';

    /**
     * Types of execution.
     */
    const TYPE_SUCCESS = 'success';
    
    /**
     * Margin of overlapping tolerance as to avoid delta gaps.
     * In minutes
     */
    const MARGIN = 10;

    /**
     * Types of execution.
     */
    const TYPE_FAILURE = 'failure';

    /**
     * Mysql datetime object for the Zend_Date formatting method.
     * Will be used to store execution information into the database.
     */
    const MYSQL_DATETIME = 'yyyy-MM-dd HH:mm:ss';
    
    /*
     * In case a class is used for more than one synch it should implement
     * the getSynchronizationCode method with the logic as to
     * differentiate
     */
    const EXPECTED_METHOD = 'getSynchronizationCode';
    
    var $_locale = null;
    var $_tz = null;

    /**
     * Basic Magento ActiveRecord initialization
     */
    protected function _construct()
    {
        $this->_init('netstarter_shelltools/synchronization');
        $this->_locale = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
        $this->_tz     = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
        
        
        // sets start time
        $startDate = Zend_Date::now();

        // gets default local/timezone
        $startDate->setLocale($this->_locale)
                  ->setTimezone($this->_tz);
        
        $this->setStartDate($startDate);
    }

    /**
     * Generates Zend_Date object based on input parameters from cron/shell
     *
     * @param $obj Class that is running the API sync.
     * @param $timeframe Date parameter from the cron/shell.
     * @return Zend_Date
     */
    public function processDate($obj, $timeframe)
    {
        $dateObj = false;
        
        if ($timeframe == self::LAST_SUCCESS_KEYORD)
        {
            $lastRunDate = $this->getResource()->getLastSuccessRun($this->_getCode($obj));
            
            // last successfull run
            if ($lastRunDate)
            {
                // last successful run found in database yay
                $dateObj = new Zend_Date();
                
                // gets default local/timezone
                $dateObj->setLocale($this->_locale)
                        ->setTimezone($this->_tz);
                
                $dateObj->set($lastRunDate);
                
                // gets delta from a specified before margin since last run
                $dateObj->sub(self::MARGIN, Zend_Date::MINUTE);
            }
            else // default 24 hours earlier
            {
                // in case of first run use default timeframe
                $dateObj = Zend_Date::now();
                
                // gets default local/timezone
                $dateObj->setLocale($this->_locale)
                        ->setTimezone($this->_tz);
                
                $dateObj->sub(self::DEFAULT_TIMEFRAME_HOURS, Zend_Date::HOUR);
            }
        }
        else
        {
            // timeframe specified in the cron/shell parameter
            $dateObj = Zend_Date::now();
            
            // gets default local/timezone
            $dateObj->setLocale($this->_locale)
                    ->setTimezone($this->_tz);
            
            $dateObj->sub(intval($timeframe), Zend_Date::HOUR);
        }
        
        // updates current object date
        $this->setLastSyncParamDate($dateObj->get(self::MYSQL_DATETIME));
        
        return $dateObj;
    }

    /**
     * This is called after cron/shell execution to update database structure.
     * For this current API sync class, we set last execution date, if it
     * was successful or not, in order to use this information on the next try.
     *
     * @param $obj
     * @param string $type
     * @param string $info
     * @return mixed
     */
    public function updateRun($obj, $type = self::TYPE_FAILURE, $info = '')
    {
        // current convention to differentiate into API sync events
        // is based on the class name of each class that implements the API sync
        $code           = $this->_getCode($obj);
        $lastParamData  = $this->getLastSyncParamDate();
        $startDate      = $this->getStartDate();
        
        $this->load($code);
        $this->setLastSyncParamDate($lastParamData);

        if ($this->getEventCode() != $code)
        {
            $this->setEventCode($code);
        }

        if ($type == self::TYPE_SUCCESS)
        {
            $this->setLastSuccessDate($startDate);
        }

        $save = $this->setLastSyncDate($startDate)
                        ->setStatus($type)
                        ->setInfo($info)
                        ->save();

        return $save;
    }

    /**
     * For standard clearInstance() call and model reuse.
     *
     * @return $this
     */
    protected function _clearReferences()
    {
        foreach ($this->_data as $data){
            if (is_object($data) && method_exists($data, 'reset')){
                $data->reset();
            }
            if (is_object($data) && method_exists($data, 'clearInstance')){
                $data->clearInstance();
            }
        }
        return $this;
    }

    /**
     * For standard clearInstance() call and model reuse.
     *
     * @return $this
     */
    protected function _clearData()
    {
        $this->setData(array());
        return $this;
    }
    
    protected function _getCode($obj)
    {
        $code = "";
        if (method_exists($obj, self::EXPECTED_METHOD))
        {
            $code = $obj->getSynchronizationCode();
        }
        
        if ($code == "")
        {
            $code = get_class($obj);
        }
        
        return $code;
    }
}