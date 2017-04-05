<?php
/**
 * @category    Netstarter
 * @package     Netstarter_Checkout
 * @copyright   http://www.netstarter.com.au
 * @license     http://www.netstarter.com.au
 */


/**
 * Wishlist sidebar block
 *
 * @category    Netstarter
 * @package     Netstarter_Checkout
 * @author      http://www.netstarter.com.au
 */
class Netstarter_Checkout_Block_Cart_Sidebar extends Mage_Checkout_Block_Cart_Sidebar
{
    protected $_cartItemsConfig;
    /**
     * Get array of last added items
     *
     * @return array
     */
    public function getRecentItems($count = null)
    {
        $this->_cartItemsConfig['cart_qty'] = $this->getQuote()->getItemsQty();
        $this->_cartItemsConfig['cart_subtotal'] = $this->getQuote()->getSubtotal();


        if (!$this->getSummaryCount()) {
            return array();
        }
        if ($count === null) {
            $count = $this->getItemCount();
        }
        return array_slice(array_reverse($this->getItems()), 0, $count);
    }

    public function getCartItemsConfig () {
        if ($this->_cartItemsConfig) {
            return $this->_cartItemsConfig;
        }
        return false;
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $item
     * @return string
     * Overridden to generate a Cart Configuraton Data array to be used outside (e.g SLICART cookie)
     */
    public function getItemHtml(Mage_Sales_Model_Quote_Item $item)
    {
        $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
        $optionList = $renderer->getOptionList();

        $itemConfig = array();
        $itemConfig['sku'] = $item->getSku();
        $itemConfig['image_url'] = strVal(Mage::helper('catalog/image')->init($item->getProduct(), 'thumbnail'));
        $itemConfig['title'] = $item->getName();
        $itemConfig['size'] = $optionList;
        $itemConfig['qty'] = $item->getQty();
        $itemConfig['price'] = $item->getRowTotal();
        $itemConfig['url'] = $item->getProduct()->getProductUrl();

        $this->_cartItemsConfig['products'][] = $itemConfig;

        return $renderer->toHtml();
    }
}
