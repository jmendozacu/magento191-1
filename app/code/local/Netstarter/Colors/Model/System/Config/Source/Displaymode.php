<?php
class Netstarter_Colors_Model_System_Config_Source_Displaymode
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => Mage::helper('colors')->__('Use Color Code')),
            array('value' => 2, 'label' => Mage::helper('colors')->__('Use Image')),
        );
    }
}
?>