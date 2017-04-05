<?php

class Netstarter_Tbyb_Model_Carrier_Freeshipping
    extends Mage_Shipping_Model_Carrier_Freeshipping
{
    
    /**
     * FreeShipping Rates Collector
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        $this->_updateFreeMethodQuote($request);
        
        
        $freeShippingStore = false;
        if (Mage::helper('core')->isModuleEnabled('Netstarter_Storeorder'))
        {
            $storeModel = Mage::getModel("storeorder/store");
            
            if (is_object($storeModel))
            {
                if ($storeModel->validateStore())
                {
                    $freeShippingStore = true;
                }
            }
        }

        
        if (($request->getFreeShipping()) || ($freeShippingStore)
            || ($request->getBaseSubtotalInclTax() >= $this->getConfigData('free_shipping_subtotal'))
        ) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('freeshipping');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('freeshipping');
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice('0.00');
            $method->setCost('0.00');

            $result->append($method);
        }

        return $result;
    }
}