<?php
/**
 * @author Prasad
 *
 * *******************************************
 * THIS IS SET FOR MAGENTO FULL PAGE CACHE
 * *******************************************
 *
 * Class Netstarter_Location_IndexController
 */
class Netstarter_Location_IndexController extends Mage_Core_Controller_Front_Action
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
        $this->loadLayout();
        $this->getLayout()->createBlock('location/breadcrumbs');
        $this->renderLayout();
    }

    protected function _initStore($storeId)
    {
        $store = Mage::getModel('location/main')->load($storeId);

        return $store;
    }

    /**
     * location detail page
     */
    public function viewAction()
    {
        $storeId = $this->getRequest()->getParam('store_id');

        if($storeId && $store = $this->_initStore($storeId)){

            Mage::register('current_store', $store);
            $this->loadLayout();
            $this->getLayout()->createBlock('location/breadcrumbs');
            $this->renderLayout();

        }else{

            $this->_forward('noRoute');
        }
    }
}
