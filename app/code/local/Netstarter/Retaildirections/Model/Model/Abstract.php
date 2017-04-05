<?php

/**
 * Class Netstarter_Retaildirections_Model_Model_Abstract
 *
 * CHANGES IN THIS FILE SHOULD BE DISCUSSED AS IT AFFECTS ALL API CLIENTS WRITTEN SO FAR.
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 */
abstract class Netstarter_Retaildirections_Model_Model_Abstract extends Netstarter_Retaildirections_Model_Abstract
{
    /**
     * Connection singleton.
     * @var $_connection Netstarter_Retaildirections_Model_Client_Connection
     */
    protected $_connection = null;

    /**
     * Standard XML node for complex calls.
     * IMPORTANT: Should always be used as a root node when passing a SimpleXmlElement
     * as a parameter to a method call.
     */
    const XML_ROOT_NODE = '<root />';
    
    protected $_isValidConnection = false;
    
    private function _validateConnection ()
    {
        $this->getConnectionModel();
        
        if (!($this->getConnectionModel()->getClient() instanceof SoapClient))
        {
            return;
        }
        
        $this->_isValidConnection = true;
    }
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_validateConnection();
    }

    /**
     * Connection singleton.
     *
     * @return Netstarter_Retaildirections_Model_Client_Connection
     */
    public function getConnectionModel()
    {
        if ($this->_connection == null)
        {
            $this->_connection = Mage::getModel('netstarter_retaildirections/client_connection');
            $this->_connection->setLogModel($this->_logModel);
        }
        return $this->_connection;
    }
    
    public function setLogModel($logModel)
    {
        if ($this->_logReportMode == self::LOG_REPORT_MODE_EMAIL)
        {
            $this->_logModel->destructWithoutEmail(true);
        }
        $this->_logModel = $logModel;
        $this->_connection->setLogModel($this->_logModel);
    }
    
    /**
     * @var $_syncModel Netstarter_Retaildirections_Model_Synchronization
     */
    protected $_syncModel = null;

    /**
     * @return Netstarter_Retaildirections_Model_Synchronization
     */
    protected function _getSynchronizationModel()
    {
        if ($this->_syncModel == null)
        {
            $this->_syncModel = Mage::getModel('netstarter_shelltools/synchronization');
        }
        return $this->_syncModel;
    }
    
    public function update($timeParam = null)
    {
        $sync = $this->_getSynchronizationModel();
        // $timeParam = $timeParam == null ? Netstarter_Shelltools_Model_Synchronization::LAST_SUCCESS_KEYORD : $timeParam;
        
        if (!$this->_isValidConnection)
        {
            $message = "ERROR: can't connect to the API.";
            
            $this->_log(array($this->_jobId, $message));
            $sync->updateRun(
                $this,
                Netstarter_Shelltools_Model_Synchronization::TYPE_FAILURE,
                $message
            );
            
            return;
        }
        
        $successfulLocking = false;
        if ($this->_lock)
        {
            $successfulLocking = $this->_initLocking();
            
            if (!$successfulLocking)
            {
                $message = "ERROR: can't lock process, or process already locked.";
                        
                $this->_log(array($this->_jobId, $message));
                $sync->updateRun(
                    $this,
                    Netstarter_Shelltools_Model_Synchronization::TYPE_FAILURE,
                    $message
                );
                
                return;
            }
        }
        
        try
        {
            $timeframe = $sync->processDate($this, $timeParam);
            $this->_update($timeframe);
            
            $sync->updateRun(
                $this,
                Netstarter_Shelltools_Model_Synchronization::TYPE_SUCCESS
            );
        }
        catch (Exception $e)
        {
            $sync->updateRun(
                $this,
                Netstarter_Shelltools_Model_Synchronization::TYPE_FAILURE,
                $e->getMessage()
            );
        }
        
        if ($this->_lock)
        {
            $this->_finishLocking();
        }
    }
}