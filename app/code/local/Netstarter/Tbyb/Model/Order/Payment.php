<?php

class Netstarter_Tbyb_Model_Order_Payment extends Mage_Sales_Model_Order_Payment
{
    public function getTbybChecker()
    {
        if (is_null($this->_tbybChecker))
        {
            $this->_tbybChecker = Mage::getModel("storeorder/store");
        }
        return $this->_tbybChecker;
    }
    
    /**
     * Create new invoice with maximum qty for invoice for each item
     * register this invoice and capture
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    protected function _invoice()
    {
        $qtys = array();
        
        foreach ($this->getOrder()->getAllItems() as $orderItem)
        {
            if ($orderItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
            {
                $sku = $orderItem->getProduct()->getData("sku");
            }
            elseif ($orderItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            {
                //$sku = $orderItem->getParentItem()->getProduct()->getData("sku");
                if (is_object($orderItem->getParentItem()))
                {
                    $sku = $orderItem->getParentItem()->getProduct()->getData("sku");
                }
                else
                {
                    $sku = $orderItem->getProduct()->getData("sku");
                }

            }
            
            if ($this->getTbybChecker()->isTryBeforeYouBuyProducts($sku))
            {
                $qtys[$orderItem->getId()] = 0;
            }
            else
            {
                $qtys[$orderItem->getId()] = $orderItem->getQtyToInvoice();
            }
        }
        
        $invoice = $this->getOrder()->prepareInvoice($qtys);

        $invoice->register();
        if ($this->getMethodInstance()->canCapture()) {
            $invoice->capture();
        }

        $this->getOrder()->addRelatedObject($invoice);
        return $invoice;
    }
}