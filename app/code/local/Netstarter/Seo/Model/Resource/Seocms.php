<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gayan
 * Date: 7/15/13
 * Time: 9:26 AM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_Seo_Model_Resource_Seocms extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        //$this->_isPkAutoIncrement = false;
        $this->_init('netstarter_seo/seocms', 'id');
    }


}