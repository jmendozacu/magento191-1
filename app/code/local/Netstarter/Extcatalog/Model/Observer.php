<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 8/28/13
 * Time: 12:08 PM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_Extcatalog_Model_Observer
{

    public function updateProduct(Varien_Event_Observer $observer)
    {
        $product = $observer->getData('product');
        $product->setRandKey($product->getId().time());
    }

    public function loadExtraFieldsFromFlat(Varien_Event_Observer $observer) {
        $select = $observer->getData('select');
        $select->columns('custom_link_url');
    }
}