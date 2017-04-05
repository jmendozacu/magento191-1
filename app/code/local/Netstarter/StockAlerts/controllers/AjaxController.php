<?php
class Netstarter_StockAlerts_AjaxController extends Mage_Core_Controller_Front_Action {

    public function subscribeAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->getBlock('stockalert.stock.subscribe');

        $productId  = (int) $this->getRequest()->getParam('product_id');
        if (!$productId) {
            $block->setMessage('error', $this->__('Not enough parameters.'));
        }

        if (!$product = Mage::getModel('catalog/product')->load($productId)) {

            $returnArr = array('error'=>true, 'success'=> false, 'msg'=> 'Invalid Product.');

            echo json_encode($returnArr);
            die();
        }

        try {
            $customerSession = Mage::getSingleton('customer/session');

            if($customerSession->isLoggedIn()){

                $customerId = Mage::getSingleton('customer/session')->getId();
                $wishlist = Mage::getModel('wishlist/wishlist');
                $wishlist->loadByCustomer($customerId, true);

                $buyRequest = new Varien_Object(array('product'=>$product->getId()));

                $result = $wishlist->addNewItem($product, $buyRequest);

                if (is_string($result)) {
                    Mage::throwException($result);
                }
                $wishlist->save();

                $model = Mage::getModel('productalert/stock')
                    ->setCustomerId(Mage::getSingleton('customer/session')->getId())
                    ->setProductId($product->getId())
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                $model->save();

                $block->setMessage('success', $this->__('Subscription has been saved.'));
            } else {

                $email = trim($this->getRequest()->getParam('email'));
                $customer = Mage::getModel('customer/customer');
                $customer->setData('website_id', Mage::app()->getStore()->getWebsiteId());

                $customerData = $customer->loadByEmail($email)->getData();
                $model = Mage::getModel('productalert/stock');

                if(!empty($customerData)){

                    $model->setCustomerId($customerData['entity_id'])
                        ->setProductId($product->getId())
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                }else{

                     $model->load($email, 'customer_email')->getData();

                    $model->setCustomerId(0)
                        ->setCustomerEmail($email)
                        ->setGuestCustomer(1)
                        ->setProductId($product->getId())
                        ->setStatus(0)
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                }

                $model->save();

                $block->setMessage('success', $this->__('Subscription has been saved.'));
            }
        }
        catch (Exception $e) {

            $block->setMessage('error', $this->__('Unable to update the subscription.'));
        }

        $response = array();
        $response['subscribeForm'] = $block->toHtml();
        echo Mage::helper('core')->jsonEncode($response);
        die();
    }

    public function checkAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->getBlock('stockalert.stock.form');
        $response = array();
        $response['notifyForm'] = $block->toHtml();
        $response['outStockSizes'] = $block->getJsonOutStockSizes();
        echo Mage::helper('core')->jsonEncode($response);
        die();
    }


}