<?php
class Netstarter_StockAlerts_Block_Catalog_Product_Edit_Tab_Alerts_Renderer_Customer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if($row->getGuestCustomer()){

            return "Guest";
        }else{
            return "Logged In";
        }
    }
}