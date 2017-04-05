<?php
class Netstarter_Groupedoptions_Model_Observer {

    /**
     * Fixes parent products associations.  Adding multiple configurable products to the cart
     *
     * observers: sales_quote_product_add_after
     *
     * @param $observer
     */
    public function fixConfigurableParentProductIds($observer)
    {

        $items      = $observer->getItems();

        $itemMap    = array();
        foreach($items as $item) {
            $itemMap[$item->getProductId()] = $item;
        }

        foreach($items as $item) {
            $product = $item->getProduct();

            if(!$item->getId() && $item->getParentItem()) {
                if ($product->getParentProductId() != $item->getParentItem()->getProduct()->getId()) {
                    //  The wrong parent item is set.
                    if (array_key_exists($product->getParentProductId(), $itemMap)) {
                        $item->setParentItem($itemMap[$product->getParentProductId()]);
                    }
                }
            }
        }
    }


    /**
     * depending on the group typ add handler to change the design
     *
     * @param $observer
     * @return Netstarter_Groupedoptions_Model_Observer
     */
    public function addHandles($observer)
    {
        $product = Mage::registry('current_product');

        if ($product instanceof Mage_Catalog_Model_Product
            && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
            $update = Mage::getSingleton('core/layout')->getUpdate();

            ($product->getGroupGiftVoucher())?$update->addHandle('PRODUCT_TYPE_grouped_gift'):$update->addHandle('PRODUCT_TYPE_grouped_looks');
        }
        return $this;
    }
}