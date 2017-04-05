<?php

class Netstarter_Eway_Model_Observer
{
    public function destroyTokenOnCheckoutFailure ()
    {
        if (!Mage::helper("netstarter_eway")->isEnabled()) { return true; }
        
        $token = Mage::registry("netstarter_eway_current_token");
        
        if (is_object($token))
        {
            Mage::getModel("netstarter_eway/api_rapid31")->destroyToken($token);
        }
    }
}