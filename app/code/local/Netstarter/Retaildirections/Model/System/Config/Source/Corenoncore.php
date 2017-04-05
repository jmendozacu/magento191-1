<?php

/**
 * Class Netstarter_Retaildirections_Model_System_Config_Source_Corenoncore
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 *
 */
class Netstarter_Retaildirections_Model_System_Config_Source_Corenoncore
{
    const CORE = 0;
    const NONCORE = 1;
    
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            self::CORE =>  'Core',
            self::NONCORE =>  'Non-core'
        );

        return $options;
    }
}