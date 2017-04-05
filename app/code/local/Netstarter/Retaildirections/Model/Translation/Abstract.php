<?php

class Netstarter_Retaildirections_Model_Translation_Abstract extends Netstarter_Retaildirections_Model_Abstract
{
    public function update($date = null)
    {
        $successfulLocking = false;
        if ($this->_lock)
        {
            $successfulLocking = $this->_initLocking();
            
            if (!$successfulLocking)
            {
                $this->_log(array($this->_jobId, "ERROR: can't lock process, or process already locked."));
                return;
            }
        }

        $this->_update($date);
        
        if ($this->_lock)
        {
            $this->_finishLocking();
        }
    }
}