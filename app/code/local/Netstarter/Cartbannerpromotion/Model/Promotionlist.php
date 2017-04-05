<?php
/**
 * Created by PhpStorm.
 * User: Tuan
 * Date: 3/20/14
 * Time: 2:00 PM
 */

class Netstarter_Cartbannerpromotion_Model_Promotionlist extends Mage_Core_Model_Abstract {

    /**
     * Representation value of enabled Cart Promotion
     *
     */
    const STATUS_ENABLED = 1;

    /**
     * Representation value of disabled Cart Promotion
     *
     */
    const STATUS_DISABLED  = 0;

    protected function _construct()
    {
        $this->_init(
            'cartbannerpromotion/promotionlist' # model class name
        );
    }
} 