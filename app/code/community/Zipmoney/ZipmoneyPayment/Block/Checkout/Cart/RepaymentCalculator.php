<?php

class Zipmoney_ZipmoneyPayment_Block_Checkout_Cart_RepaymentCalculator extends Zipmoney_ZipmoneyPayment_Block_Abstract
{
 

    public function _prepareLayout()
    {		
    	if ($this->_isZipMoneyPaymentActive() &&   $this->_isActive()){
            $this->setTemplate('zipmoney/zipmoneypayment/catalog/product/view/rep-calculator.phtml');
        }
        
    }
    
    private function _isActive()
    {
        return Mage::getStoreConfig(Zipmoney_ZipmoneyPayment_Model_Config::PAYMENT_WIDGET_CONFIGURATION_REP_CALC_ACTIVE_CART);
    }

    public function getTotal()
    {	
        $amount = 0;
    
    	if($quote = Mage::getModel('checkout/cart')->getQuote()) 
    		$amount = $quote->getGrandTotal();
    	    		
        return $amount;
    }
    
}
