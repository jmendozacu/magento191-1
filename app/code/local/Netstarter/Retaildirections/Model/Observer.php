<?php

class Netstarter_Retaildirections_Model_Observer
{

    /**
     * customer_save_before event to save customer data
     *
     * event : customer_save_commit_after
     * api call : CustomerEdit
     *
     * @param $observer
     *
     * @return $this
     */
    public function afterCustomerSave($observer)
    {

        $event = $observer->getEvent();
        $customer = $event->getCustomer();
        
        $rdCustomer = Mage::getModel('netstarter_retaildirections/model_customer');

        if(Mage::registry('is_passed')){

            return $this;
        }else{
            Mage::register('is_passed', 1);
        }

        /**
         *
         * is customer a guest then create customer in RD
         *
         */
        if ($customer->isObjectNew() || $customer->hasDataChanges()) {

            // $rdCustomer = Mage::getModel('netstarter_retaildirections/model_customer');

            $rdCustomer->createCustomer($customer);
        }

        /**
         * has changes to pw
         */
//        if($customer->hasData('password')){
//
//            $rdCustomer->resetPassword($customer->getEmail(), $customer->getLastname(), $customer->getPassword());
//        }

        return $this;
    }


    /**
     * customer address save
     *
     * event : customer_address_save_commit_after
     * api call : CustomerSiteEdit
     *
     * @param $observer
     */
    public function afterCustomerAddressSave($observer)
    {

        $address = $observer->getCustomerAddress();
        $customer = $address->getCustomer();
        
        $rdCustomer = Mage::getModel('netstarter_retaildirections/model_customer');
        $logModel = $rdCustomer->getLogModel();

        if($address->isObjectNew() || $address->hasDataChanges() || $address->hasOriginalDataChanges()){

            $defaultBilling = $customer->getDefaultBillingAddress();
            $defaultSipping = $customer->getDefaultShippingAddress();

            if(($defaultBilling && $address->getEntityId() == $defaultBilling->getId())
                || ($defaultSipping && $address->getEntityId() == $defaultSipping->getId()))
                $address->setDefaultAddress(true);

            $rdCustomer->createCustomerSite($customer,$address);
        }
    }


    /**
     * @param $observer
     */
    public function orderSaveAfter($observer)
    {

        $order = $observer->getOrder();
        $quote = $observer->getQuote();

        if($order && $order->getEntityId()){

            $currentStoreId = Mage::app()->getStore()->getStoreId();

            if($currentStoreId == Mage_Core_Model_App::ADMIN_STORE_ID){
                Mage::app()->setCurrentStore($order->getStoreId());
            }

            $rdOrders = Mage::getModel('netstarter_retaildirections/model_orders');

            if ($order && $quote) {
                $rdOrders->createOrders($order, $quote);
            }

            if($currentStoreId == Mage_Core_Model_App::ADMIN_STORE_ID){
                Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            }
        }
    }

    /**
     * Inventory check order save
     *
     * @param $observer
     * @return $this
     */
    public function checkInventorySaveOrder($observer)
    {
        if(Mage::registry('has_inventory_checked')){

            return $this;
        }else{
            Mage::register('has_inventory_checked', 1);
        }

        $order = $observer->getOrder();

        $rdProducts = Mage::getModel('netstarter_retaildirections/model_orders');
        $webSiteId = (int)Mage::app()->getStore()->getWebsiteId();
        $orderItems = $order->getAllItems();

        $rdProducts->getProductStock($orderItems, $webSiteId, 1);

        return $this;
    }

    /**
     * Inventory check checkout start
     *
     * @param $observer
     * @return $this
     */
    public function checkInventoryCheckoutInit($observer)
    {

        if(Mage::registry('has_inventory_checked')){

            return $this;
        }else{

            Mage::register('has_inventory_checked', 1);
        }

        try{

            $checkoutSession = Mage::getSingleton('checkout/session');

            $returnItems = array();

            foreach($checkoutSession->getQuote()->getAllVisibleItems() as $item){

                $stockItem = $item->getProduct()->getStockItem();
                $isManageStock = (int) $stockItem->getManageStock();

                if($isManageStock){

                    if($item->getHasChildren()) {

                        foreach($item->getChildren() as $child) {
                            $returnItems[$child->getProductId()] = array('sku' => $child->getSku(),
                                'qty' => $item->getQty(),
                                'name' => $child->getName());
                        }

                    } else {

                        $returnItems[$item->getProductId()] = array('sku' => $item->getSku(),
                            'qty' => $item->getQty(),
                            'name' => $item->getName());
                    }
                }
            }

            if(!empty($returnItems)){

                $rdProducts = Mage::getModel('netstarter_retaildirections/model_orders');
                $webSiteId = (int)Mage::app()->getStore()->getWebsiteId();
                $rdProducts->getProductStock($returnItems, $webSiteId, 0);
            }

            return $this;

        }catch (Exception $e){

            Mage::logException($e);
        }
    }

    /**
     * checkout cart add, refresh stock items
     *
     * @param $observer
     */
    public function cartProductAdd($observer)
    {

        $item = $observer->getQuoteItem();

        $checkoutSession = Mage::getSingleton('checkout/session');
        $cartItems = ($checkoutSession->hasCartItems())?$checkoutSession->getCartItems(): array();
        $cartItems[$item->getProductId()] = array('sku' => $item->getSku(),'qty' => $item->getQty(), 'name' => $item->getName());
        $checkoutSession->setData('cart_items', $cartItems);
    }

    /**
     * checkout cart remove, refresh stock items
     *
     * @param $observer
     */
    public function cartProductRemove($observer)
    {
        $item = $observer->getQuoteItem();
        $checkoutSession = Mage::getSingleton('checkout/session');
        $cartItems = ($checkoutSession->hasCartItems())?$checkoutSession->getCartItems(): array();

        $isParent = $item->getHasChildren();

        $proId = null;
        if($isParent){
            $children = $item->getChildren();
            if(isset($children[0])){

                $child= $children[0];
                $proId = $child->getProductId();
            }
        }else{

            $proId = $item->getProductId();
        }

        if(isset($cartItems[$proId])) unset($cartItems[$proId]);
        $checkoutSession->setData('cart_items', $cartItems);
    }

    /**
     * checkout cart update, refresh stock items
     *
     * @param $observer
     */
    public function cartProductsUpdate($observer)
    {

        $info = $observer->getInfo();

        $checkoutSession = Mage::getSingleton('checkout/session');
        $quote = $checkoutSession->getQuote();

        $cartItems = ($checkoutSession->hasCartItems())?$checkoutSession->getCartItems(): array();

        foreach($info as $itemId => $itemInfo){

            $item = $quote->getItemById($itemId);

            if($item){

                $isParent = $item->getHasChildren();
                $proId = null;

                if($isParent){
                    $children = $item->getChildren();
                    if(isset($children[0])){

                        $child= $children[0];
                        $proId = $child->getProductId();
                    }
                }else{

                    $proId = $item->getProductId();
                }

                if($proId){

                    if (!empty($itemInfo['remove']) || (isset($itemInfo['qty']) && $itemInfo['qty']=='0')) {

                        if(isset($cartItems[$proId])) unset($cartItems[$proId]);
                        continue;
                    }

                    $qty = isset($itemInfo['qty']) ? (float) $itemInfo['qty'] : false;
                    if ($qty > 0) {
                        if(isset($cartItems[$proId])){
                            $cartItems[$proId]['qty'] = $qty;
                        }
                    }
                }
            }
        }

        $checkoutSession->setData('cart_items', $cartItems);
    }

    /**
     * create RD gift cards
     *
     * @param $observer
     * @return $this
     */
    public function createGiftCard($observer)
    {
        $card = $observer->getRequest();
        $code = $observer->getCode();

        $rdGiftCard = Mage::getModel('netstarter_retaildirections/model_giftcard');
        $rdGiftCard->createGiftCard($card, $code);

        return $this;
    }
}