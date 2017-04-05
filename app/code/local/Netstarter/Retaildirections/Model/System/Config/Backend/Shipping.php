<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 9/26/13
 * Time: 1:45 AM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_Retaildirections_Model_System_Config_Backend_Shipping extends Mage_Core_Model_Config_Data
{

    protected function _beforeSave() {
        parent::_beforeSave();
        $groups = $this->getData('groups');
        $values = $groups["{$this->getGroupId()}"]['fields'][$this->getField()]['value'];

        unset($values['__empty']);

        if (is_array($values)) {

            $data = array();
            foreach ($values as $groupId => $method) {
                if (!array_key_exists($method['shipping_code'], $data)) {
                    $data[$method['shipping_code']] = $method;
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