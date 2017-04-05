<?php

class Netstarter_Tbyb_Model_Source_Payment
{
    const XML_PATH = 'curvesence/tbyb/pament_methods';
    
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'netstarter_eway_rapid31',
                'label' => 'Eway Rapid 3.1'
            ),
        );
    }
}