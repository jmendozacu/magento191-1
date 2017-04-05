<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 10/17/13
 * Time: 10:28 AM
 * Remove comparelist being cache, to avoid cookie issue
 *
 */ 
class Netstarter_Extcatalog_Model_PageCache_Container_Sidebar_Comparelist extends Enterprise_PageCache_Model_Container_Sidebar_Comparelist
{

    /**
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param null $lifetime
     * @return bool|Enterprise_PageCache_Model_Container_Abstract
     *
     * Remove comparelist being cache, to avoid cookie issue
     */
    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false;
    }

}