<?php

class Netstarter_Eway_Model_Api_Rapid31
{
    private $_url = null;
    private $_username = null;
    private $_password = null;
    
    const REDIRECT_URL = "http://www.brasnthings.com";
    
    /*
     * Visa 4444333322221111
     * MasterCard 5105105105105100
     */
    
    public function getConnection()
    {
        $connection = Mage::getModel("netstarter_eway/api_connection");
        
        $connection->setUrl($this->getUrl());
        $connection->setUsername($this->getUsername());
        $connection->setPassword(Mage::helper('core')->decrypt($this->getPassword()));
        
        return $connection;
    }
    
    public function getUsername()
    {
        if ($this->_username == null)
        {
            if (!Mage::getStoreConfigFlag("payment/netstarter_eway_rapid31/test"))
            {
                $this->_username = Mage::getStoreConfig("payment/netstarter_eway_rapid31/live_username");
            }
            else
            {
                $this->_username = Mage::getStoreConfig("payment/netstarter_eway_rapid31/sandbox_username");
            }
        }
        
        return $this->_username;
    }
    
    public function getPassword()
    {
        if ($this->_password == null)
        {
            if (!Mage::getStoreConfigFlag("payment/netstarter_eway_rapid31/test"))
            {
                $this->_password = Mage::getStoreConfig("payment/netstarter_eway_rapid31/live_password");
            }
            else
            {
                $this->_password = Mage::getStoreConfig("payment/netstarter_eway_rapid31/sandbox_password");
            }
        }
        
        return $this->_password;
    }
    
    public function getUrl()
    {
        if ($this->_url == null)
        {
            if (!Mage::getStoreConfigFlag("payment/netstarter_eway_rapid31/test"))
            {
                $this->_url = Mage::getStoreConfig("payment/netstarter_eway_rapid31/live_url");
            }
            else
            {
                $this->_url = Mage::getStoreConfig("payment/netstarter_eway_rapid31/sandbox_url");
            }
        }
        
        return $this->_url;
    }
    
    
    public function createToken(Varien_Object $payment)
    {
        $connection = $this->getConnection();

        $order = $payment->getOrder();
        $customer = $payment->getOrder()->getCustomer();
        
        $billingAddress = $customer->getDefaultBillingAddress();

        if (!($customer->getId() > 0))
        {
            Mage::throwException("Guest checkout is not allowed, please contact customer support.");
        }
        
        $country = "";
        if ($billingAddress == false)
        {
            $country = $payment->getOrder()->getBillingAddress()->getCountry();
        }
        else
        {
            $country = $billingAddress->getCountry();
        }
        
        $name = "";
        if ($billingAddress == false)
        {
            $name = $payment->getOrder()->getBillingAddress()->getName();
        }
        else
        {
            $name = $billingAddress->getName();
        }

        /*
         * Call 1
         */
        $paramsAccessCode = new stdClass();

        $paramsAccessCode->Payment = new stdClass();
        $paramsAccessCode->Payment->TotalAmount = "0";

        $paramsAccessCode->RedirectUrl = self::REDIRECT_URL;
        $paramsAccessCode->Method = "CreateTokenCustomer";

        $paramsAccessCode->Customer = new stdClass();
        $paramsAccessCode->Customer->Reference    = $customer->getId();
        $paramsAccessCode->Customer->FirstName    = $customer->getFirstname();
        $paramsAccessCode->Customer->LastName     = $customer->getLastname();
        $paramsAccessCode->Customer->Email        = $customer->getEmail();
        $paramsAccessCode->Customer->Country      = $country;

        $resultAccessCode = $connection->CreateAccessCode($paramsAccessCode);
        
        if (!property_exists($resultAccessCode, 'AccessCode'))
        {
            Mage::throwException("Could not process customer with payment gateway.");
        }


        /*
         * Call 2
         */
        $paramsPostCreditCardData = array();
        $paramsPostCreditCardData["EWAY_ACCESSCODE"] = $resultAccessCode->AccessCode;
        $paramsPostCreditCardData["EWAY_CARDNAME"] = $name;
        $paramsPostCreditCardData["EWAY_CARDNUMBER"] = $payment->getCcNumber();
        $paramsPostCreditCardData["EWAY_CARDEXPIRYMONTH"] = $payment->getCcExpMonth();
        $paramsPostCreditCardData["EWAY_CARDEXPIRYYEAR"] = $payment->getCcExpYear();
        $paramsPostCreditCardData["EWAY_CARDCVN"] = $payment->getCcCid();

        $resultPostCreditCardData = $connection->PostCreditCardData($resultAccessCode->FormActionURL, $paramsPostCreditCardData);


        /*
         * Call 3
         */
        $resultRequestResults = $this->_getAccessCodeResult($resultAccessCode->AccessCode);

        if (!property_exists($resultRequestResults, 'TokenCustomerID'))
        {
            Mage::throwException("Could not process payment with payment gateway.");
        }
        

        /*
         * Save token
         */
        $token = Mage::getModel("netstarter_eway/token");
        $token->setCustomerId($customer->getId());
        $token->setCustomerEmail($customer->getEmail());
        $token->setToken($resultRequestResults->TokenCustomerID);
        $token->setWebsiteId(Mage::getModel('core/store')->load($order->getStoreId())->getWebsiteId());
        $token->setCurrencyCode($resultAccessCode->Payment->CurrencyCode);
        $token->setAccessCode($resultAccessCode->AccessCode);

        try
        {
            $token->save();
            Mage::register("netstarter_eway_current_token", $token);
        }
        catch (Exception $e)
        {
            $this->destroyToken($token);
            Mage::throwException($e);
        }

        return $token;
    }
    
    public function payWithToken(Varien_Object $token, $amount, $customer = null)
    {
        if (!is_object($token))
        {
            Mage::throwException("Can't charge customer or customer already charged.");
        }
        
        if (is_null($customer))
        {
            $customer = Mage::getModel("customer/customer")->load($token->getCustomerId());
        }
        
        $connection = $this->getConnection();
        $amount = (string) ($amount*100);
        
        
        /*
         * Call 1
         */
        $paramsAccessCode = new stdClass();

        $paramsAccessCode->Payment = new stdClass();
        $paramsAccessCode->Payment->TotalAmount = $amount;

        $paramsAccessCode->RedirectUrl = self::REDIRECT_URL;
        $paramsAccessCode->Method = "TokenPayment";
        $paramsAccessCode->TransactionType = "Recurring";

        $paramsAccessCode->Customer = new stdClass();
        $paramsAccessCode->Customer->TokenCustomerID = $token->getToken();

        $resultRequestResults = $connection->ChargeCustomer($paramsAccessCode);
        
        return $resultRequestResults;
    }
    
    public function destroyToken(Varien_Object $token)
    {
        $connection = $this->getConnection();
        
        /*
         * Call 1
         */
        $paramsAccessCode = new stdClass();

        $paramsAccessCode->Payment = new stdClass();
        $paramsAccessCode->Payment->TotalAmount = "0";

        $paramsAccessCode->RedirectUrl = self::REDIRECT_URL;
        $paramsAccessCode->Method = "UpdateTokenCustomer";
        $paramsAccessCode->TransactionType = "Recurring";

        $paramsAccessCode->Customer = new stdClass();
        $paramsAccessCode->Customer->TokenCustomerID = $token->getToken();

        $resultAccessCode = $connection->CreateAccessCode($paramsAccessCode);
        
        
        /*
         * Call 2
         */
        $paramsPostCreditCardData = array();
        $paramsPostCreditCardData["EWAY_ACCESSCODE"] = $resultAccessCode->AccessCode;
        $paramsPostCreditCardData["EWAY_CARDNAME"] = "INVALID CREDIT CARD";
        $paramsPostCreditCardData["EWAY_CARDNUMBER"] = "4444333322221111";
        $paramsPostCreditCardData["EWAY_CARDEXPIRYMONTH"] = $resultAccessCode->Customer->CardExpiryMonth;
        $paramsPostCreditCardData["EWAY_CARDEXPIRYYEAR"] = $resultAccessCode->Customer->CardExpiryYear;

        $resultPostCreditCardData = $connection->PostCreditCardData($resultAccessCode->FormActionURL, $paramsPostCreditCardData);
        
        
        /*
         * Call 3
         */
        $resultRequestResults = $this->_getAccessCodeResult($resultAccessCode->AccessCode);
        
        return $resultRequestResults;
    }
    
    private function _getAccessCodeResult($resultAccessCode)
    {
        $connection = $this->getConnection();

        $paramsRequestResults = new stdClass();
        $paramsRequestResults->AccessCode = $resultAccessCode;

        $resultRequestResults = $connection->GetAccessCodeResult($paramsRequestResults);
        
        return $resultRequestResults;
    }
}