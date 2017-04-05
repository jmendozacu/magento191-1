<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 9/17/13
 * Time: 2:28 PM
 * To change this template use File | Settings | File Templates.
 */ 
class Netstarter_Checkout_Block_Checkout_Onepage extends Mage_Checkout_Block_Onepage
{
    protected function _getStepCodes()
    {
        return array('login', 'billing', 'shipping', 'shipping_method', 'payment');
    }
}