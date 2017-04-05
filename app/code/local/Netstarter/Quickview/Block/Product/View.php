<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dilhan
 * Date: 6/19/13
 * @namespace   : Netstarter
 * @Module      : Netstarter_Quickview
 */

class Netstarter_Quickview_Block_Product_View extends Mage_Catalog_Block_Product_View
{

    public function getQuickViewIdentifier() {

        return strVal(rand(0, 1000));
    }

    public function isCartPage() {
        $isCartPage = false;
        if ($this->getRequest()->getParam('pageType') == 'cart') {
            $isCartPage = true;
        }
        return $isCartPage;
    }
}