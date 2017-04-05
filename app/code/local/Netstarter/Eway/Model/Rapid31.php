<?php

class Netstarter_Eway_Model_Rapid31 extends Mage_Payment_Model_Method_Cc
{
    protected $_code = 'netstarter_eway_rapid31';
    
    protected $_formBlockType = 'netstarter_eway/form_cc';
    
    protected $_isGateway               = true;
    protected $_canAuthorize            = false;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
    protected $_canSaveCc               = false;
    protected $_canRefundInvoicePartial = false;
    
    protected $_tbybChecker             = null;
    protected $_token                   = null;
    
    public function getAdapter ()
    {
        return Mage::getModel("netstarter_eway/api_rapid31");
    }
    
    public function getTbybChecker()
    {
        if (is_null($this->_tbybChecker))
        {
            $this->_tbybChecker = Mage::getModel("storeorder/store");
        }
        return $this->_tbybChecker;
    }
    
    public function getToken()
    {
        if ($this->_token === null)
        {
            $this->_token = Mage::getModel('netstarter_eway/token')
                    ->loadByCustomerId(
                        $this->_getCustomerId(),
                        Mage::app()->getWebsite()->getId()
                    );
        }
        return $this->_token;
    }
    
    private function _getCustomerId(){
        $info = $this->getInfoInstance();
        if($info->getOrder() == null){
            return Mage::getSingleton('customer/session')->getCustomer()->getId();
        }else{
            return $info->getOrder()->getCustomerId();
        }
    }
    
    public function setToken(Varien_Object $token)
    {
        $this->_token = $token;
        return $this;
    }
    
    public function isValidToken()
    {
        if (!is_object($this->getToken()))
        {
            return false;
        }
        
        if (!($this->getToken()->getId() > 0))
        {
            return false;
        }
        
        return true;
    }
    
    public function validate()
    {
        if (!$this->isValidToken())
        {
            parent::validate();
        }
        return $this;
    }
    
    public function authorize(Varien_Object $payment, $amount)
    {
        parent::authorize($payment, $amount);
        return $this;
    }

    public function capture(Varien_Object $payment, $amount)
    {
        parent::capture($payment, $amount);
        
        if (!$this->isValidToken())
        {
            $token = $this->createToken($payment);
        }
        else
        {
            $token = $this->getToken();
        }
        
        
        if ($amount > 0)
        {
            $result = $this->payWithToken($token, $amount, $payment->getOrder()->getCustomer());

            $payment->setTransactionId($result->TransactionID);
            $payment->setAdditionalInformation($result->TransactionID);
            
            $status = $result->TransactionStatus;
        }
        else
        {
            $status = true;
        }
        
        
        
        
        
        if (Mage::getStoreConfigFlag("payment/netstarter_eway_rapid31/test"))
        {
            $forced = Mage::getStoreConfig("payment/netstarter_eway_rapid31/sandbox_forced");
            switch($forced)
            {
                case Netstarter_Eway_Model_Source_Forced::NO_CHANGE:
                    break;
                case Netstarter_Eway_Model_Source_Forced::FORCE_SUCCESS:
                    $status = true;
                    break;
                case Netstarter_Eway_Model_Source_Forced::FORCE_FAILURE:
                    $status = false;
                    break;
            }
        }
        
        
        if (!$status)
        {
            $responseProcessor = Mage::getModel("netstarter_eway/api_rapid31_responsecodes");
            
            $message = "";
            if (property_exists($result, "ResponseMessage"))
            {
                $message = $responseProcessor->processAndReturn($result->ResponseMessage);
            }
            
            // process proper message.
            Mage::throwException(
                "Payment gateway error, please contact our customer support team : " .
                $message
            );
        }

        return $this;
    }
    
    public function createToken(Varien_Object $payment)
    {
        $token = $this->getAdapter()->createToken($payment);
        return $token;
    }
    
    public function payWithToken(Varien_Object $token, $amount)
    {
        return $this->getAdapter()->payWithToken($token, $amount);
    }
    
    public function destroyToken(Varien_Object $token)
    {
        return $this->getAdapter()->destroyToken($token);
    }
}