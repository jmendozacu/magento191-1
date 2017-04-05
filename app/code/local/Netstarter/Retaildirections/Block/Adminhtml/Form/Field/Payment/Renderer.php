<?php

class Netstarter_Retaildirections_Block_Adminhtml_Form_Field_Payment_Renderer extends Mage_Core_Block_Html_Select
{
    /**
     * Customer groups cache
     *
     * @var array
     */
    private $_paymentMethods;

    /**
     * Flag whether to add group all option or no
     *
     * @var bool
     */
    protected $_addGiftCards = true;

    /**
     * Retrieve allowed customer groups
     *
     * @param int $groupId  return name by customer group id
     * @return array|string
     */
    protected function _getPaymentMethods($groupId = null)
    {
        if (is_null($this->_shippingMethods)) {
            $this->_shippingMethods = array();
            $configDataModel = Mage::getSingleton('adminhtml/config_data');
            $methods = Mage::getSingleton('payment/config')->getActiveMethods($configDataModel->getScopeId());

            foreach ($methods as $code => $method) {

                if(!$title = Mage::getStoreConfig("payment/$code/title"))
                    $title = $code;

                if($code == 'anz_egate' || $code == 'ccsave' ){
                    $creditCardTypes =  Mage::getStoreConfig("payment/$code/cctypes");
                    if($creditCardTypes){
                        $cards = explode(',',$creditCardTypes);

                        foreach($cards as $card){
                            $codeI = $code.$card;
                            $this->_paymentMethods[$codeI] = "{$title} {$code} {$card}";
                        }
                    }

                }else{

                    if(!$title = Mage::getStoreConfig("payment/$code/title"))
                        $title = $code;

                    $this->_paymentMethods[$code] = $title;
                }


            }

            if ($this->_addGiftCards) {

                $methodsGc = Mage::getSingleton('giftcardapi/config')->getActiveMethods($configDataModel->getScopeId());
                foreach ($methodsGc as $code => $method) {

                    if(!$title = Mage::getStoreConfig("giftcardapi/$code/name"))
                        $title = $code;

                    $this->_paymentMethods[$code] = "{$title} Gift Cards";
                }
            }
        }
        if (!is_null($groupId)) {
            return isset($this->_paymentMethods[$groupId]) ? $this->_paymentMethods[$groupId] : null;
        }
        return $this->_paymentMethods;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {

            foreach ($this->_getPaymentMethods() as $code => $label) {
                $this->addOption($code, addslashes($label));
            }
        }
        return parent::_toHtml();
    }
}
