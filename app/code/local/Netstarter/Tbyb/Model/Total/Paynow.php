<?php
class Netstarter_Tbyb_Model_Total_Paynow extends Netstarter_Tbyb_Model_Total_Abstract
{
    protected $_code = 'netstarter_tbyb_paynow';
 
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
            $finalTotal = $this->_calculateValueTbyb ($address, false);
            
            $this->_latestCalculated = array(
                    'code'  =>  $this->getCode(),
                    'title' =>  'Charged today',
                    'value' =>  $finalTotal
            );
            
            $address->addTotal($this->_latestCalculated);
        }
        return $this;
    }
}