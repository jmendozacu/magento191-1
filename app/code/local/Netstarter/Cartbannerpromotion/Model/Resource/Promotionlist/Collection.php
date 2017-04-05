<?php
/**
 * Netssubscribe resource collection model
 *
 * @category Netstarter
 * @package  Netstarter_Netssubscribe
 * @author   http://www.netstarter.com.au/
 * @license  http://www.netstarter.com.au//license.txt
 * @link     N/A
 */
class Netstarter_Cartbannerpromotion_Model_Resource_Promotionlist_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Link domain model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cartbannerpromotion/promotionlist');
    }
}