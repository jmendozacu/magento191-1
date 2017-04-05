<?php

class Zipmoney_ZipmoneyPayment_Block_Catalog_Product_RepaymentCalculator extends Zipmoney_ZipmoneyPayment_Block_Catalog_Product_Abstract
{
 
    public function _prepareLayout()
    {		
        if ($this->_isZipMoneyPaymentActive() &&   $this->_isActive()){
            $this->setTemplate('zipmoney/zipmoneypayment/catalog/product/view/rep-calculator.phtml');
        }

    }

    private function _isActive()
    {
        return Mage::getStoreConfig(Zipmoney_ZipmoneyPayment_Model_Config::PAYMENT_WIDGET_CONFIGURATION_REP_CALC_ACTIVE_PRODUCT);
    }

    public function getTotal()
    {	
        $amount = 0;
    	if($product = $this->getProduct())
    		$amount = (float)$this->getProduct()->getFinalPrice();
        
        return $amount;
    }


    
}
