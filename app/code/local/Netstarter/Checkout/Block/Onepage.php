<?php
/**
 * Onepage checkout block
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author     <http://www.netstarter.com.au>
 */
class Netstarter_Checkout_Block_Onepage extends Mage_Checkout_Block_Onepage
{
    /**
     * Get checkout steps codes
     * Overridden from Mage_Checkout_Block_Onepage_Abstract
     * @return array
     */
    protected function _getStepCodes()
    {
        return array('login', 'billing', 'shipping', 'shipping_method', 'payment');
    }
}
