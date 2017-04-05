<?php

/**
 * Class Netstarter_Shelltools_Model_Log
 *
 * Handles all logging.
 *
 * CHANGES IN THIS FILE SHOULD BE DISCUSSED AS IT AFFECTS ALL API CLIENTS WRITTEN SO FAR.
 *
 * @category  Netstarter
 * @package   Netstarter_Shelltools
 */
class Netstarter_Shelltools_Model_Log
{
    /**
     * @var $_helper null|Netstarter_Shelltools_Helper_Data
     */
    protected $_helper = null;
    
    protected $_logFileName = null;

    /**
     * XML path to Magento configuration: should it log?
     */
    const CONFIG_PATH_LOG_IS_ACTIVE = 'netstarter_shelltools/log/is_active';

    /**
     * XML path to Magento configuration: which filename to use?
     */
    const CONFIG_PATH_LOG_FILE_NAME = 'netstarter_shelltools/log/log_file';

    /**
     * The character used when imploding logging arrays
     */
    const LOG_SEPARATOR = ' | ';

    /**
     * The job name added to the log, to be easily seen.
     */
    protected $_logJobName = '';

    public function __construct($fileName = null)
    {
        if (is_string($fileName))
        {
            $this->_logFileName = $fileName;
        }
    }
    
    public function setId($id)
    {
        $this->_logJobName = $id;
    }

    public function getLogFileName()
    {
        if ($this->_logFileName == null)
        {
            $this->_logFileName = Mage::getStoreConfig(self::CONFIG_PATH_LOG_FILE_NAME);
        }
        
        return $this->_logFileName;
    }
    
    public function setLogFileName($fileName)
    {
        $this->_logFileName = $fileName;
    }

    /**
     * @return Netstarter_Shelltools_Helper_Data
     */
    protected function _getHelper()
    {
        if ($this->_helper == null)
        {
            $this->_helper = Mage::helper('netstarter_shelltools');
        }
        return $this->_helper;
    }

    /**
     * Logs and format log based on an array or string
     * Uses Mage::log
     *
     * @param  array|string $message
     * @return int
     */
    public function log($message = array(), $level = null)
    {
        if (!Mage::getStoreConfigFlag(self::CONFIG_PATH_LOG_IS_ACTIVE)) return;
        if (!is_array($message)) $message = array($message);

        // Zend_Log::DEBUG level as standard
        $level = $level == null ? Zend_Log::DEBUG : $level;

        // apply translation
        array_walk($message, array($this->_getHelper(), '__'));
        
        // adds memory usage information to the logging
        $finalMessage = implode(self::LOG_SEPARATOR,
                            array_merge(
                                array($this->_logJobName, $this->_getMemoryUsage()),
                                $message
                            )
                        );
        
        Mage::log(
            $finalMessage,
            $level,
            $this->getLogFileName()
        );
    }

    /**
     * Calculates available memory in MB
     * Rounds up
     *
     * @return string
     */
    protected function _getMemoryUsage()
    {
        return ceil(memory_get_usage()/1024/1024) . "MB";
    }
}