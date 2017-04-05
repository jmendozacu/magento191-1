<?php
/**
 * Class Netstarter_Shelltools_Model_Lock
 *
 * Locking mechanism
 *
 * CHANGES IN THIS FILE SHOULD BE DISCUSSED AS IT AFFECTS ALL API CLIENTS WRITTEN SO FAR.
 *
 * @category  Netstarter
 * @package   Netstarter_Shelltools
 */
class Netstarter_Shelltools_Model_Lock
{
    /**
     * Process lock properties
     */
    protected $_isLocked = null;
    protected $_lockFile = null;

    protected $_prefix = 'process_';
    protected $_id = null;

    public function setPrefix ($prefix)
    {
        $this->_prefix = $prefix;
    }

    public function getPrefix ()
    {
        return $this->_prefix;
    }

    public function setId ($id)
    {
        $this->_id = $id;
    }

    public function getId ()
    {
        return $this->_id;
    }

    protected function _getLockFile()
    {
        if ($this->_lockFile === null) {
            $varDir = Mage::getConfig()->getVarDir('locks');
            $file = $varDir . DS . $this->getPrefix () . $this->getId() . '.lock';
            if (is_file($file)) {
                $this->_lockFile = fopen($file, 'w');
            } else {
                $this->_lockFile = fopen($file, 'x');
            }
            fwrite($this->_lockFile, date('r'));
        }
        return $this->_lockFile;
    }

    /**
     * Lock process without blocking.
     * This method allow protect multiple process runing and fast lock validation.
     *
     * @return Netstarter_Shelltools_Model_Lock
     */
    public function lock()
    {
        $this->_isLocked = true;
        flock($this->_getLockFile(), LOCK_EX | LOCK_NB);
        return $this;
    }

    /**
     * Lock and block process.
     * If new instance of the process will try validate locking state
     * script will wait until process will be unlocked
     *
     * @return Netstarter_Shelltools_Model_Lock
     */
    public function lockAndBlock()
    {
        $this->_isLocked = true;
        flock($this->_getLockFile(), LOCK_EX);
        return $this;
    }

    /**
     * Unlock process
     *
     * @return Netstarter_Shelltools_Model_Lock
     */
    public function unlock()
    {
        $this->_isLocked = false;
        flock($this->_getLockFile(), LOCK_UN);
        return $this;
    }

    /**
     * Check if process is locked
     *
     * @return bool
     */
    public function isLocked()
    {
        if ($this->_isLocked !== null) {
            return $this->_isLocked;
        } else {
            $fp = $this->_getLockFile();
            if (flock($fp, LOCK_EX | LOCK_NB)) {
                flock($fp, LOCK_UN);
                return false;
            }
            return true;
        }
    }

    /**
     * Close file resource if it was opened
     */
    public function __destruct()
    {
        if ($this->_lockFile) {
            fclose($this->_lockFile);
        }
    }
}