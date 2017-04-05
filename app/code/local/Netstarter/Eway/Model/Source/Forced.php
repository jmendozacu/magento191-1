<?php

class Netstarter_Eway_Model_Source_Forced
{
    const NO_CHANGE = '0';
    const FORCE_SUCCESS = '1';
    const FORCE_FAILURE = '2';
    
    public function toOptionArray()
    {
            return array(
                    array(
                            'value' => '0',
                            'label' => 'No change'
                    ),
                    array(
                            'value' => '1',
                            'label' => 'Force Success'
                    ),
                    array(
                            'value' => '2',
                            'label' => 'Force Failure'
                    )
            );
    }
}