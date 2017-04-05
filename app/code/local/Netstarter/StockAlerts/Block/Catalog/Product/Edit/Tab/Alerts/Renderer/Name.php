<?php
class Netstarter_StockAlerts_Block_Catalog_Product_Edit_Tab_Alerts_Renderer_Name extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {

        $value = $this->getColumn()->getIndex();
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToSelect($value)->addFieldToFilter('entity_id', $row->getData('customer_id'))->getFirstItem();

        return $collection->getData($value);

    }
}