<?php

/**
 * Improved Sftp client
 *
 * @category  Netstarter
 * @package   Netstarter_Exacttarget
 *
 * Class Netstarter_Exacttarget_Model_Io_Sftp
 */
class Netstarter_Exacttarget_Model_Io_Sftp extends Varien_Io_Sftp
{
    /**
     * A write function that actually pass along the mode...
     * 
     * Write a file
     * @param $src Must be a local file name
     */
    public function write($filename, $src, $mode=null)
    {
        return $this->_connection->put($filename, $src, $mode);
    }
}