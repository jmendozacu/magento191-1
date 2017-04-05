<?php
class Netstarter_Tbyb_Model_Status
{
    const STATUS_TOBECHARGED  = '1';
    const STATUS_CANCELLED    = '2';
    const STATUS_SUCCESS     = '3';
    const STATUS_FAILURE      = '4';
    
    public function getOptionsArray()
    {
        return array(
            self::STATUS_TOBECHARGED => "To be charged",
            self::STATUS_CANCELLED => "Cancelled",
            self::STATUS_SUCCESS => "Success",
            self::STATUS_FAILURE => "Failure",
        );
    }
}