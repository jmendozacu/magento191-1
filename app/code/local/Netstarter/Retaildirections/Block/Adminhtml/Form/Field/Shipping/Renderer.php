<?php

class Netstarter_Retaildirections_Block_Adminhtml_Form_Field_Shipping_Renderer extends Mage_Core_Block_Html_Select
{
    /**
     * Customer groups cache
     *
     * @var array
     */
    private $_shippingMethods;

    /**
     * Flag whether to add group all option or no
     *
     * @var bool
     */
    protected $_addGroupAllOption = true;

    /**
     * Retrieve allowed customer groups
     *
     * @param int $groupId  return name by customer group id
     * @return array|string
     */
    protected function _getShippingMethods($groupId = null)
    {
        if (is_null($this->_shippingMethods)) {
            $this->_shippingMethods = array();

            $configDataModel = Mage::getSingleton('adminhtml/config_data');
            $methods = Mage::getSingleton('shipping/config')->getActiveCarriers($configDataModel->getScopeId());

            foreach ($methods as $code => $method) {

                if(!$title = Mage::getStoreConfig("carriers/$code/title"))
                    $title = $code;

                $code .= "_$code";
                $this->_shippingMethods[$code] = $title;
            }
        }
        if (!is_null($groupId)) {
            return isset($this->_shippingMethods[$groupId]) ? $this->_shippingMethods[$groupId] : null;
        }
        return $this->_shippingMethods;
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
            foreach ($this->_getShippingMethods() as $code => $label) {
                $this->addOption($code, addslashes($label));
            }
        }
        return parent::_toHtml();
    }
}
