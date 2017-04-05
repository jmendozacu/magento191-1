<?php

/**
 * Default helper.
 *
 * @category  Netstarter
 * @package   Netstarter_Importimages
 *
 * Class Netstarter_Importimages_Helper_Data
 */
class Netstarter_Importimages_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function arrayCallback(&$k, &$v)
    {
        $k = str_replace(PHP_EOL,'',print_r($k, true));
        $v = str_replace(PHP_EOL,'',print_r($v, true));
    }
}