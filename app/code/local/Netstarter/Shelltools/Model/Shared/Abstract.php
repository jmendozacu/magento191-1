<?php

/**
 * Class Netstarter_Shelltools_Model_Lock_Abstract
 * Email only, not filesystem logging for now.
 *
 * @category  Netstarter
 * @package   Netstarter_Shelltools
 */
abstract class Netstarter_Shelltools_Model_Shared_Abstract
{
    protected $_logModel                = null;
    protected $_lockModel                = null;
    protected $_jobId                   = null;
    protected $_lock                    = true;
    
    /*
     * Lock process
     */
    protected function _initLocking()
    {
        if ($this->_lock)
        {
            $this->_lockModel = Mage::getModel("netstarter_shelltools/lock");
            $this->_lockModel->setPrefix($this->_jobId);
            
            if ($this->_lockModel->isLocked())
            {
                return false;
            }
                    
            $this->_lockModel->lock();
            
            return true;
        }
        
        return false;
    }
    
    /*
     * Finish locking
     */

    protected function _finishLocking()
    {
        if ($this->_lock)
        {
            $this->_lockModel->unlock();
        }
    }
    
    /*
     * Public entry point
     */
    public function update()
    {
        $successfulLocking = false;
        if ($this->_lock)
        {
            $successfulLocking = $this->_initLocking();
            
            if (!$successfulLocking)
            {
                $message = "ERROR: can't lock process, or process already locked.";
                        
                $this->_log(array($this->_jobId, $message));
                return;
            }
        }
        
        $this->_update();
        
        if ($this->_lock)
        {
            $this->_finishLocking();
        }
    }   
    
    /*
     * Init email logging
     * 
     * @TODO support filesystem log
     * 
     */
    protected function _initLogging()
    {
        $this->_logModel = Mage::getModel("netstarter_shelltools/email");
        $this->_logModel->setId($this->_jobId);
    }

    /**
     * @param array|string $message
     * @param int $level
     */
    protected function _log($message = array(), $level = null)
    {
        if ($this->_logModel == null)
        {
            $this->_initLogging();
        }
        
        $this->_logModel->log($message, $level);
    }
}