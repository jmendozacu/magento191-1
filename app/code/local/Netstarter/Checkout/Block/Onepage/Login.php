<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dilhan
 * Date: 6/19/13
 * @namespace   : Netstarter
 * @Module      : Netstarter_Quickview
 */

class Netstarter_Checkout_Block_Onepage_Login extends Mage_Checkout_Block_Onepage_Login
{
    protected $_customer = null;

    /**
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer() {
        if ($this->_customer instanceof Mage_Customer_Model_Customer) {
            return $this->_customer;
        }else {
            return Mage::getModel('customer/customer');
        }
    }

    public function setCustomer($customer) {
        $this->_customer = $customer;
    }

    public function getUserEmail () {
        $params = $this->getRequest()->getParam('login');
        return isset($params['username']) ? $params['username'] : '';
    }
}