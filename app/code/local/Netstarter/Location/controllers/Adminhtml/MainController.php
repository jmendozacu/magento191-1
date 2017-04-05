<?php

class Netstarter_Location_Adminhtml_MainController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }
    public function indexAction()
    {
        $this->loadLayout();
        $storeData = Mage::getModel('location/main')->load(0);
        $storeData->setLocationId(0);

        $infoData = $storeData->loadInfo();
        $infoData->setStoreId(0);

        $storeData->addData($infoData->getData());

        Mage::register('info_data', $storeData);

        $this->renderLayout();
    }


    public function saveAction()
    {
        if ($this->getRequest()->getPost()){

            try{

                $postData = $this->getRequest()->getPost();
                $infoData = $postData['store'];

                if($infoData){

                    $locationModel = Mage::getModel('location/main');
                    $infoData['store_id'] = 0;
                    $infoModel = Mage::getModel('location/info');
                    $infoModel->setData($infoData);
                    $locationModel->saveInfo($infoModel);
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Main Page data saved successfully saved'));
                Mage::getSingleton('adminhtml/session')->setLocationData(false);

                $this->_redirect('*/*/');
                return;

            }catch (Exception $e){

                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setLocationData($this->getRequest()->getPost());

                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }

        $this->_redirect('*/*/');

    }
}