<?php

class Netstarter_Storeorder_Model_System_Config_Backend_Product extends Mage_Core_Model_Config_Data
{

    protected function _beforeSave() {
        parent::_beforeSave();
        $groups = $this->getData('groups');
        $values = $groups["{$this->getGroupId()}"]['fields'][$this->getField()]['value'];

        unset($values['__empty']);

        if (is_array($values)) {

            $data = array();
            foreach ($values as $groupId => $method) {
                if (!array_key_exists($method['product_sku'], $data)) {
                    $data[$method['product_sku']] = $method;
                }
            }

            $this->setValue(serialize($data));
        }
        else {
            $this->setValue(serialize($values));
        }
    }

    /**
     * Unserialize
     */
    protected function _afterLoad() {
        parent::_afterLoad();
        $this->setValue(unserialize($this->getValue()));
    }
}