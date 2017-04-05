<?php

/**
 * Class Netstarter_Retaildirections_Model_Abstract
 *
 * CHANGES IN THIS FILE SHOULD BE DISCUSSED AS IT AFFECTS ALL API CLIENTS WRITTEN SO FAR.
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 */
abstract class Netstarter_Retaildirections_Model_Abstract
{
    /**
     * @var $_helper Netstarter_Retaildirections_Helper_Data
     */
    protected $_helper                  = null;

    /**
     * @var $_helper null|array
     */
    protected $_websiteList             = null;

    /**
     * @var $_helper null|string
     */
    protected $_defaultWebsiteCode      = 'base';
    
    protected $_logReportMode           = null;

    protected $_logModel                = null;

    protected $_logFilename             = null;

    protected $_logXmlPath              = null;
    
    protected $_jobId                   = null;
    
    protected $_lock                    = false;
    
    const LOG_REPORT_MODE_EMAIL         = 'email';
    const LOG_REPORT_MODE_LOG           = 'log';

    /**
     * XML path for Magento configuration for default RDWS store id in Magento.
     * Initially products need this data.
     */
    const CONFIG_PATH_IS_DEFAULT        = 'netstarter_retaildirections/product/is_default';

    /**
     * XML path for Magento configuration for RDWS store id.
     */
    const CONFIG_PATH_STORE_ID          = 'netstarter_retaildirections/general/store_id';

    /**
     * XML path for Magento configuration for RDWS supply channel id.
     */
    const CONFIG_PATH_SUPPLY_CHANNEL_ID = 'netstarter_retaildirections/general/supply_channel_id';
    
    public function __construct()
    {
        $this->_initLogging();
    }

    /**
     * @return Netstarter_Retaildirections_Helper_Data
     */
    protected function _getHelper()
    {
        if ($this->_helper == null)
        {
            $this->_helper = Mage::helper('netstarter_retaildirections');
        }
        return $this->_helper;
    }

    /**
     * @return string
     */
    public function getStoreId($website = null)
    {
        if ($website != null)
        {
            return trim(Mage::getConfig()->getNode(self::CONFIG_PATH_STORE_ID, 'website', $website));
        }
        return trim(Mage::getStoreConfig(self::CONFIG_PATH_STORE_ID));
    }

    /**
     * @return string
     */
    public function getSupplyChannelId($website = null)
    {
        if ($website != null)
        {
            return trim(Mage::getConfig()->getNode(self::CONFIG_PATH_SUPPLY_CHANNEL_ID, 'website', $website));
        }
        return trim(Mage::getStoreConfig(self::CONFIG_PATH_SUPPLY_CHANNEL_ID));
    }

    /**
     * @return array
     */
    protected function _getWebsitesData()
    {
        if ($this->_websiteList == null)
        {
            $this->_websiteList = array();
            foreach (Mage::app()->getWebsites() as $website)
            {
                $isDefault = Mage::getConfig()->getNode(
                    self::CONFIG_PATH_IS_DEFAULT,
                    'website', // for this attribute we work at the website scope level
                    $website->getCode()
                );

                $isDefault = $isDefault == true;

                $this->_websiteList[$website->getCode()] = $isDefault;

                if ($isDefault)
                {
                    $this->_defaultWebsiteCode = $website->getCode();
                }
            }
        }

        return $this->_websiteList;
    }

    /**
     * @return string
     */
    protected function _getDefaultWebsiteCode()
    {
        if ($this->_defaultWebsiteCode == null)
        {
            $this->_getWebsitesData();
        }

        if ($this->_defaultWebsiteCode == null)
        {
            Mage::throwException("Please set a website as default.");
        }
        #Mage::log(print_r($this->_defaultWebsiteCode, null, 'abstract_website.log'));
	$this->_defaultWebsiteCode='base';
        return $this->_defaultWebsiteCode;
    }
    
    protected function _initLocking()
    {
        if ($this->_lock)
        {
            $this->lockModel = Mage::getModel("netstarter_shelltools/lock");
            $this->lockModel->setPrefix($this->_jobId);
            
            if ($this->lockModel->isLocked())
            {
                return false;
            }
                    
            $this->lockModel->lock();
            
            return true;
        }
        
        return false;
    }
    
    protected function _finishLocking()
    {
        if ($this->_lock)
        {
            $this->lockModel->unlock();
        }
    }
    
    protected function _initLogging()
    {
        if ($this->_logReportMode == self::LOG_REPORT_MODE_EMAIL)
        {
            $this->_logModel = Mage::getModel("netstarter_shelltools/email");
        }
        else if ($this->_logReportMode == self::LOG_REPORT_MODE_LOG)
        {
            if (is_string($this->getLogFilename()) && strlen($this->getLogFilename()) > 0)
            {
                $this->_logModel = Mage::getModel("netstarter_shelltools/log", $this->getLogFilename());
            }
            else
            {
                $this->_logModel = Mage::getModel("netstarter_shelltools/log");
            }
        }
        
        $this->_logModel->setId($this->_jobId);
    }

    protected function _log($message = array(), $level = null)
    {
        if ($this->_logModel == null)
        {
            $this->_initLogging();
        }

        $this->_logModel->log($message, $level);
    }
    
    public function doNotMail($bool = false)
    {
        if ($this->_logReportMode == self::LOG_REPORT_MODE_EMAIL)
        {
            $this->_logModel->destructWithoutEmail($bool);
            $this->_logModel->doNotKeep($bool);
        }
    }
    
    public function getLogFilename()
    {
        if ($this->_logFilename == null)
        {
            if (is_string($this->_logXmlPath) && strlen($this->_logXmlPath) > 0)
            {
                $this->_logFilename = Mage::getStoreConfig($this->_logXmlPath);
            }
        }
       
        return $this->_logFilename;
    }
    
    public function getLogModel()
    {
        if ($this->_logModel == null)
        {
            $this->_initLogging();
        }
        
        return $this->_logModel;
    }
}
