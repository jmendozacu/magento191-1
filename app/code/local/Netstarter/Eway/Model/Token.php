<?php
class Netstarter_Eway_Model_Token extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('netstarter_eway/token');
    }
    
    public function loadByCustomerId($customerId, $websiteId)
    {
        $tokenCollection = $this->getCollection();
        
        $tokenCollection->addFieldToFilter('customer_id', $customerId);
        $tokenCollection->addFieldToFilter('website_id', $websiteId);
        
        if ($tokenCollection->count() > 0)
        {
            return $tokenCollection->getFirstItem();
        }
        
        return false;
    }
    
    public function setToken($token = null)
    {
        if ($token == null)
        {
            Mage::throwException ("Payment error");
        }
        
        $token = Mage::helper('core')->encrypt(base64_encode($token));
        
       return parent::setToken($token); 
    }
    
    public function getToken()
    {
       return base64_decode(Mage::helper('core')->decrypt(parent::getToken()));
    }
}