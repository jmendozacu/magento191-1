<?php

/**
 * Class Netstarter_Importimages_Model_System_Config_Source_Fileaction
 * 
 * What are the actinos for a processed file.
 *
 * @category  Netstarter
 * @package   Netstarter_Importimages
 *
 */
class Netstarter_Importimages_Model_System_Config_Source_Fileaction
{
    const MOVE = 0;
    const DELETE = 1;
    
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            self::MOVE =>  'Move',
            self::DELETE =>  'Delete'
        );

        return $options;
    }
}