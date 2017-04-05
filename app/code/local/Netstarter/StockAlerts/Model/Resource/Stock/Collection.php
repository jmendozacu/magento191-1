<?php

class Netstarter_StockAlerts_Model_Resource_Stock_Collection extends Mage_ProductAlert_Model_Resource_Stock_Collection
{
    public function joinCustomer($productId, $websiteId)
    {

        $this->getSelect()->joinLeft(
            array('customer' => $this->getTable('customer/entity')),
            'main_table.customer_id=customer.entity_id'
        );

        $this->getSelect()->where('main_table.product_id=?', $productId);
        if ($websiteId) {
            $this->getSelect()->where('main_table.website_id=?', $websiteId);
        }
        $this->_setIdFieldName('alert_stock_id');

//        mage::log($this->getSelect()->assemble());
        return $this;
    }
}
