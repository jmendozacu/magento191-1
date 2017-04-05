<?php

/**
 * Class Netstarter_Shelltools_Model_Email
 *
 * Handles all email reports.
 *
 * CHANGES IN THIS FILE SHOULD BE DISCUSSED AS IT AFFECTS ALL API CLIENTS WRITTEN SO FAR.
 *
 * @category  Netstarter
 * @package   Netstarter_Shelltools
 */
class Netstarter_Shelltools_Model_Email
{
    /**
     * @var $_helper null|Netstarter_Shelltools_Helper_Data
     */
    protected $_helper = null;

    protected $_io    = null;
    protected $_path  = null;
    protected $_name  = null;
    
    protected $_emailIsSent   = false;
    protected $_doNotSend     = false;
    protected $_doNotKeep     = false;
    
    /**
     * Mysql datetime object for the Zend_Date formatting method.
     * Will be used to store execution information into the database.
     */
    const MYSQL_DATETIME = 'yyyy-MM-dd HH:mm:ss';

    /**
     * The character used when imploding logging arrays
     */
    const LOG_SEPARATOR = ' | ';

    /**
     * The job name added to the log, to be easily seen.
     */
    protected $_logJobName = '';
    
    protected $_duration = 0;

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
    
    protected function _initTime()
    {
        $this->_duration = microtime(true);
    }
    
    protected function _getTime()
    {
        return microtime(true) - $this->_duration;
    }
    
    protected function _getBaseDir()
    {
        return Mage::getBaseDir('var') . DS . 'email' . DS;
    }

    protected function _getStream($name = null, $mode = 'w+')
    {
        if ($this->_io == null)
        {
            $this->_io = new Varien_Io_File();

            $this->_path = $this->_getBaseDir();
            
            $resolvedName = null;
            $resolvedName = $this->_name != null && is_string($this->_name) ? $this->_name : $resolvedName;
            $resolvedName = $name != null && is_string($name) ? $name : $resolvedName;
            
            if ($resolvedName == null)
            {
                $resolvedName = date('Y_m_d_H_i_s').'_'.Mage::helper('core')->uniqHash().'.txt';
            }
            
            $this->_name = $resolvedName;

            $this->_io->setAllowCreateFolders(true);
            $this->_io->open(array('path' => $this->_path));
            $this->_io->streamOpen($this->_name, $mode);
            $this->_io->streamLock(true);
        }

        return $this->_io;
    }

    public function log($message = array())
    {
        if (!is_array($message)) $message = array($message);
        
        $dateObj = Zend_Date::now();
        // gets default local/timezone
        $dateObj->setLocale(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE))
            ->setTimezone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE));

        $str = implode(self::LOG_SEPARATOR,
            array_merge(
                array($dateObj->get(self::MYSQL_DATETIME)),
                array($this->_getMemoryUsage()),
                $message
            )
        ) . PHP_EOL;

        $this-> _getStream()->streamWrite($str);
    }

    public function getFromEmail()
    {
        return Mage::getStoreConfig('netstarter_shelltools/email/sender_email');
    }

    public function getFromName()
    {
        return Mage::getStoreConfig('netstarter_shelltools/email/sender_name');
    }

    public function getToEmail()
    {
        return explode(',',Mage::getStoreConfig('netstarter_shelltools/email/recipient_email'));
    }

    public function getSubject()
    {
        return sprintf(Mage::getStoreConfig('netstarter_shelltools/email/subject'), $this->_logJobName);
    }
    
    public function getFilename()
    {
        return $this->_name;
    }
    
    public function setFilename($name = null)
    {
        return $this->_name = $name;
    }
    
    public function setId($id)
    {
        $this->_logJobName = $id;
    }

    public function sendEmail($addDuration = true)
    {
        if ($this->_emailIsSent)
        {
            return;
        }
        
        if ($addDuration)
        {
            $this->log(array('Script duration was', $this->_getTime(), 'seconds'));
        }
        
        // get content
        $this-> _getStream()->streamUnlock();
        $this-> _getStream()->streamClose();
        $this-> _getStream()->streamOpen($this->_name, 'r');

        $body = '';
        while ($buffer = $this-> _getStream()->streamRead())
        {
            $body .= $buffer;
        }

        $this-> _getStream()->streamClose();
        
        // build email
        $mail = new Zend_Mail();

        $mail->setBodyText($body);
        $mail->setFrom($this->getFromEmail(), $this->getFromName())
            ->addTo($this->getToEmail())
            ->setSubject($this->getSubject());
        $mail->send();
        
        // remove temp file
        $this-> _getStream()->rm($this->_name);
       
        $this->_emailIsSent = true;
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
    
    public function destructWithoutEmail($bool)
    {
        $this->_doNotSend = $bool;
    }
    
    public function doNotKeep($bool)
    {
        $this->_doNotKeep = $bool;
    }
    
    public function __construct()
    {
        $this->_initTime();
    }
    
    public function __destruct()
    {
        if ($this->_doNotSend == false)
        {
            $this->sendEmail();
        }
        else
        {
            if ($this->_doNotKeep && is_string($this->_name) && strlen($this->_name) > 0)
            {
                $this-> _getStream()->rm($this->_name);
            }
        }
    }
    
    public function clearInstance()
    {
        $this->_name = null;
        $this->_emailIsSent = false;
        $this->_io = null;
    }
    
    public function clean()
    {
        $this->destructWithoutEmail(true);
        $this->_logJobName = "Email wasnt sent properly. Please check.";
        
        foreach (glob($this->_getBaseDir() . "*.txt") as $filename)
        {
            $this->_getStream($filename, 'a');
            $this->sendEmail(false);
            $this->clearInstance();
        }
    }
}