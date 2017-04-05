<?php
 
class Netstarter_Tbyb_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function cancelitemAction()
    {
        if ($this->getRequest()->isPost()) {
            $orderId = $this->getRequest()->getParam('order_id');
            $customerEmail = $this->getRequest()->getParam('customer_email');

            $success = 0;
            try {
                if ($orderId) {
                    $customersForWebsites = Mage::getModel('customer/customer')
                        ->getCollection()
                        ->addAttributeToSelect('entity_id')
                        ->addAttributeToFilter('email',$customerEmail);

                    //Mage::log($customersForWebsites->getSelect()->assemble());
                    $customerIdsForEmail = array();

                    if ($customersForWebsites->getSize()) {
                        foreach ($customersForWebsites as $customer) {
                            $customerIdsForEmail[] = $customer->getId();
                        }

                        $itemCollection = Mage::getModel('netstarter_tbyb/item')->getCollection();
                        $itemCollection->addFieldToFilter('increment_id', $orderId);
                        if ($customerIdsForEmail) {
                            $itemCollection->addFieldToFilter('customer_id', array('in' =>array('in' => $customerIdsForEmail)));
                        }

                        //Mage::log($itemCollection->getSelect()->assemble());

                        if ($itemCollection->getSize()){
                            foreach ($itemCollection as $itemModel) {
                                if ($itemModel->getStatus() == Netstarter_Tbyb_Model_Status::STATUS_TOBECHARGED) { //this is eligible to cancel
                                    $itemModel
                                        ->setStatus(Netstarter_Tbyb_Model_Status::STATUS_CANCELLED)
                                        ->setUpdatedAt(time())
                                        ->setCancelledAt(time())
                                        ->save();
                                    $success++;
                                }
                            }
                            $success++;
                        }
                    }
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }


            if ($success) {
                Mage::getSingleton('core/session')->addSuccess(
                    Mage::helper('netstarter_tbyb')->__(
                        'Curvessence Order has been Cancelled.', $success
                    )
                );
            } else {
                Mage::getSingleton('core/session')->addWarning(
                    Mage::helper('netstarter_tbyb')->__(
                        'Curvessence Order was not cancelled because required data is missing.'
                    )
                );
            }

            $this->_redirect('*/*/');
        } else {
            $this->_redirect('*/*/');
        }
    }
}