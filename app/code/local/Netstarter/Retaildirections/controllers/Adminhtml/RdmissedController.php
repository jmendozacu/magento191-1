<?php
class Netstarter_Retaildirections_Adminhtml_RdmissedController extends Mage_Adminhtml_Controller_Action
{
    /**
     * location main page
     */
    protected function _isAllowed()
    {
        return true;
    }
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('afeature/items')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
        $this->renderLayout();
    }

    public function resendAction()
    {

        $orderIds = $this->getRequest()->getParam('order_ids');

        if(!empty($orderIds)){

            $initialCount = count($orderIds);

            foreach($orderIds as $orderId){

                try{

                    Mage::app()->setCurrentStore(1);
                    $rdOrders = Mage::getModel('netstarter_retaildirections/model_orders');

                    $order = Mage::getModel('sales/order')->load($orderId);

                    Mage::app()->setCurrentStore($order->getStoreId());

                    $order->setCustomer(Mage::getModel('customer/customer')->load($order->getCustomerId()));

                    $quoteId = (int)$order->getQuoteId();
                    $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($quoteId);

                    if ($order && $quote) {

                        $rdOrders->createOrders($order, $quote);
                    }

                    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

                }catch (Exception $e){

                }
            }

            $collection = Mage::getResourceModel('sales/order_collection');
            $collection->addFieldToFilter('rd_order_code', array('null' => true));
            $collection->addFieldToFilter('entity_id', array('in' => $orderIds));


            $notUpdated =  $collection->getSize();
            $updated = $initialCount - $notUpdated;

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('netstarter_retaildirections')->__('Order Resend Complete'));

            if($updated){
                Mage::getSingleton('adminhtml/session')->addSuccess("$updated orders were being resend to RMS");
            }

            if($notUpdated){
                Mage::getSingleton('adminhtml/session')->addError("$notUpdated orders were not being resend to RMS please check the error logs");
            }

            $this->_redirect('*/*/');


        }else{
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('netstarter_retaildirections')->__('Order Ids not found'));
            $this->_redirect('*/*/');
        }
    }
}
