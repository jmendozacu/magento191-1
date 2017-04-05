<?php
/**
 * Created by PhpStorm.
 * User: Tuan
 * Date: 3/20/14
 * Time: 2:00 PM
 */

class Netstarter_Cartbannerpromotion_Model_Resource_Promotionlist extends Mage_Core_Model_Resource_Db_Abstract {
    protected function _construct()
    {
        $this->_init(
            'cartbannerpromotion/promotionlist', # model class name
            'promotion_id'
        );
    }
} 