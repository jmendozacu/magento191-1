<?php
class Netstarter_Tbyb_Block_Adminhtml_Tbyb_Grid_Column_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $array = Mage::getModel('netstarter_tbyb/status')->getOptionsArray();
        
        if (array_key_exists($row->getStatus(), $array))
        {
            return $array[$row->getStatus()];
        }
        
        return $row->getStatus();
    }
}