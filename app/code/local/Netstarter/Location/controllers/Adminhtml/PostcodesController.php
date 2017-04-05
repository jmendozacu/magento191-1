<?php
/**
 * Class Netstarter_Location_Adminhtml_PostcodesController
 */
class Netstarter_Location_Adminhtml_PostcodesController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction()
    {
        $id   = $this->getRequest()->getParam('id');
        $this->loadLayout();
        $postcodeData = Mage::getModel('location/postcode')->load($id);

        Mage::register('postcode_data', $postcodeData);

        $this->renderLayout();

    }

    public function newAction()
    {

        $this->_forward('edit');
    }


    public function saveAction()
    {
        if ($this->getRequest()->getPost()){

            try{

                $postData = $this->getRequest()->getPost();

                $locationModel = Mage::getModel('location/postcode');
                $locationModel->addData($postData);
                $locationModel->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Postcode data saved successfully saved'));
                Mage::getSingleton('adminhtml/session')->setPostcodeData(false);

                $this->_redirect('*/*/');
                return;

            }catch (Exception $e){


                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPostcodeData($this->getRequest()->getPost());

                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }

        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id')){

            try{

                $id = $this->getRequest()->getParam('id');
                $store = Mage::getModel('location/postcode')->load($id);
                $store->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Postcode data deleted successfully saved'));

                $this->_redirect('*/*/');
                return;

            }catch (Exception $e){

                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }

        $this->_redirect('*/*/');
    }
}