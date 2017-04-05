<?php
class Netstarter_Tbyb_Model_Total_Paylater extends Netstarter_Tbyb_Model_Total_Abstract
{
    protected $_code = 'netstarter_tbyb_paylater';

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        
        return $this;
    }
 
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if ($address->getAddressType() != 'billing' && $this->_getStoreModel())
        {
            return;
        }
        
        $isTbyb = $this->isTbybCart ($address);
        
        if ($isTbyb)
        {
            $finalTotal  = $this->_calculateValueTbyb ($address);
            $tobeCharged = $this->_calculateFuturePaymentDate();
            
            $this->_latestCalculated = array(
                    'code'  =>  $this->getCode(),
                    'title' =>  'To be charged on '.$tobeCharged,
                    'value' =>  $finalTotal
            );
            
            $address->addTotal($this->_latestCalculated);

        }
        return $this;
    }
}