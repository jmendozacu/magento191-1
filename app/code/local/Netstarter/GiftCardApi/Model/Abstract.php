<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 9/11/13
 * Time: 1:04 PM
 * To change this template use File | Settings | File Templates.
 */
abstract class Netstarter_GiftCardApi_Model_Abstract extends Varien_Object
{

    protected $_giftCardCode;
    protected $_pinCode;

    protected $_modelCode;

    public function setGiftCardCode($giftCardCode)
    {
        $this->_giftCardCode = $giftCardCode;
    }

    public function setPinCode($pinCode)
    {
        $this->_pinCode = $pinCode;
    }

    public function getSettingConfig($param, $store = null)
    {
        $path = "{$this->_modelCode}/gcsetting{$param}";
        $config = Mage::getStoreConfig($path, $store);

        return $config;
    }

    public function getModelCode()
    {
        return $this->_modelCode;
    }

    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'giftcardsapi/'.$this->getModelCode().'/'.$field;
        return Mage::getStoreConfig($path, $storeId);
    }

}