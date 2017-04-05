<?php

class Netstarter_Storeorder_Model_Store extends Mage_Core_Model_Abstract
{
    protected $_tryBeforeYouBuyProductList = "";
    public function validateStore(){
        return Mage::helper("netstarter_tbyb")->isEnabled() && in_array($_SERVER["REMOTE_ADDR"], Mage::helper('storeorder/data')->getStoreIPAddress());     
    }
    
    public function isTryBeforeYouBuyProducts($sku){
        if(!Mage::helper("netstarter_tbyb")->isEnabled())
            return false;
        if(!$this->_tryBeforeYouBuyProductList)
            $this->_tryBeforeYouBuyProductList = unserialize(Mage::getStoreConfig("curvesence/storeorder/productlist"));
        return isset($this->_tryBeforeYouBuyProductList[$sku]);  
    }
    
    public function cartHasTbybProducts(){
        if(!Mage::helper("netstarter_tbyb")->isEnabled())
            return false;
        $session = Mage::getSingleton('checkout/session');
        foreach($session->getQuote()->getAllItems() as $item){
            $p = $item->getProduct();
            if($p->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE && $this->isTryBeforeYouBuyProducts($p["sku"]))
                return true;
        }
        return false;
    }
    
    public function quoteHasTbybProducts(Mage_Sales_Model_Quote_Address $address)
    {
        foreach($address->getQuote()->getAllItems() as $item)
        {
            $p = $item->getProduct();
            if($this->isTryBeforeYouBuyProducts ($p['sku']))
                return true;
        }
        
        return false;
    }
    
    public function calculateFuturePaymentDate($createdAt){
        return strtotime("+" . intval(Mage::getStoreConfig("curvesence/tbyb/nooffuturepaymentday")) + 1 . " day", strtotime($createdAt));     
    }
}