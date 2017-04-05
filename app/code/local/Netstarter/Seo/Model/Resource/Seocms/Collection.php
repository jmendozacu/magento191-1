<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gayan
 * Date: 7/15/13
 * Time: 9:28 AM
 * To change this template use File | Settings | File Templates.
 */

class Netstarter_Seo_Model_Resource_Seocms_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize collection
     */
    public function _construct()
    {
        $this->_init('netstarter_seo/seocms');
    }
}