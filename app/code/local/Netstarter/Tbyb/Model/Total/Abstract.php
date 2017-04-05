<?php
class Netstarter_Tbyb_Model_Total_Abstract extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    protected $_store = null;
    protected $_latestCalculated = null;

    protected $_useOrderDate = false;

    public function setUseOrderDate($useOrderDate = false)
    {
        $this->_useOrderDate = $useOrderDate;
    }

    public function getLatestCalculated()
    {
        return $this->_latestCalculated;
    }

    protected function _getStoreModel()
    {
        if ($this->_store == null)
        {
            $this->_store = Mage::getModel("storeorder/store");
            if (!is_object($this->_store)) $this->_store = false;
        }

        return $this->_store;
    }

    protected function _calculateFuturePaymentDate()
    {
        if ($this->_useOrderDate)
        {
            $date = $this->_useOrderDate;
        }
        else
        {
            $date = date("Y-m-d h:i:s");
        }

        return date("d/m/Y", $this->_getStoreModel()->calculateFuturePaymentDate($date));
    }

    protected function _isTbybSku ($p)
    {
        return $this->_getStoreModel()->isTryBeforeYouBuyProducts($p['sku']);
    }

    public function isTbybCart (Mage_Sales_Model_Quote_Address $address)
    {
        return $this->_getStoreModel()->quoteHasTbybProducts($address);
    }

    protected function _calculateValueTbyb (Mage_Sales_Model_Quote_Address $address, $condition = true)
    {
        $items = $this->_getAddressItems($address);

        if (count($items) == 0)
        {
            $items = $address->getQuote()->getAllItems();
        }

        $tbybTotal = 0.0;
//        $isTbyb = false;

        foreach ($items as $item)
        {
            $p = $item->getProduct();

            $tbybCondition = $this->_isTbybSku ($p) && $p->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE;

            if ($condition)
            {
                if($tbybCondition == $condition)
                {
//                    $isTbyb = true;
                    $tbybTotal += $item->getPriceInclTax() * $item->getQty();
                }
            }
            else
            {
                if($tbybCondition == $condition)
                {
                    $tbybTotal += $item->getPriceInclTax() * $item->getQty();
                }
            }
        }

//        if ($isTbyb)
//        {
//            return $tbybTotal;
//        }

        return $tbybTotal;
    }
}