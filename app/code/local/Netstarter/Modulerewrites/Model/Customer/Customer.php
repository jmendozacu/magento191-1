<?php

/**
 * Customer model
 *
 * @category    Netstarter
 * @package     Netstarter_Modulerewrites
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Netstarter_Modulerewrites_Model_Customer_Customer extends Mage_Customer_Model_Customer
{
    /*
     * ADDED FOR THE GLOBAL PASSWORD
     */
    const XML_NODE_GLOBALPASSWORD = 'customer/netstarter_globalpassword/password';
    
    public function authenticateUsingGlobalPassword($password)
    {
        return Mage::helper('core')->validateHash($password, Mage::getStoreConfig(self::XML_NODE_GLOBALPASSWORD));
    }
    /*
     * END ADDED FOR THE GLOBAL PASSWORD
     */
    
    /**
     * Authenticate customer
     *
     * @param  string $login
     * @param  string $password
     * @throws Mage_Core_Exception
     * @return true
     *
     */
    public function authenticate($login, $password)
    {
        $customer = $this->loadByEmail($login);

        Mage::getSingleton('customer/session')->setData('passreset_email', $login);

        if ($this->getConfirmation() && $this->isConfirmationRequired()) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('This account is not confirmed.'),
                self::EXCEPTION_EMAIL_NOT_CONFIRMED
            );
        }
        if(!$customer->getEntityId()){

            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Email Address Not Found. <a href="/customer/account/create">Please click here to create an account</a> for our new website'.'<script type="text/javascript">dataLayer.push({\'eventCategory\': \'Login Errors\', \'eventAction\': \'User Not Found\', \'eventLabel\': '.$login.', \'event\': \'GAevent\'}); </script>'));
        }

    /*
     * ADDED FOR THE GLOBAL PASSWORD
     */
        if ($this->authenticateUsingGlobalPassword($password)) {
        }
    /*
     * END ADDED FOR THE GLOBAL PASSWORD
     */
        else if (!$this->validatePassword($password)) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('
                Incorrect password, please try again or  <a href="/customer/account/forgotpassword/">reset your password</a><script type="text/javascript">dataLayer.push({\'eventCategory\': \'Login Errors\', \'eventAction\': \'Invalid Password\', \'eventLabel\': '.$password.', \'event\': \'GAevent\'}); </script>')
        );
        }
        Mage::dispatchEvent('customer_customer_authenticated', array(
           'model'    => $this,
           'password' => $password,
        ));

        return true;
    }
    public function sendNewAccountEmail($type = 'registered', $backUrl = '', $storeId = '0')
    {

    }
}
