<?php

class Netstarter_Location_Adminhtml_LocationController extends Mage_Adminhtml_Controller_Action
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
        $storeData = Mage::getModel('location/main')->load($id);

        if(!$storeData->getLocationId()) $storeData->setActive(1);

        $infoData = $storeData->loadInfo();
        Mage::register('store_data', $storeData);
        Mage::register('info_data', $infoData);

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
                $mainData = $postData['main'];
                $infoData = $postData['store'];

                $write = Mage::getSingleton('core/resource')->getConnection('core_write');
                $write->beginTransaction();

                if($mainData){

                    if(empty($mainData['location_id'])) unset($mainData['location_id']);
                    $mainData['flag'] = (isset($mainData['flag']))?1:0;
                    if(empty($mainData['identifier'])) {
                        $mainData['identifier'] = $mainData['name'];
                    }
                    $mainData['identifier'] = strtolower(str_replace(' ','-',$mainData['identifier']));

                    $locationModel = Mage::getModel('location/main');
                    $locationModel->addData($mainData);
                    $locationModel->save();

                    $infoData['store_id'] = $locationModel->getLocationId();


                    if(!empty($infoData['store_id'] )){

                        if (isset($_FILES['store_image']['name']) && file_exists($_FILES['store_image']['tmp_name'])){

                            $fileSize= $_FILES['store_image']['size'];

                            if ($fileSize > 500000) {

                                Mage::getSingleton("adminhtml/session")->addError(Mage::helper("location")->__("Image is too big."));
                                $this->_redirect("*/*/edit", array("id" => $infoData['store_id']));
                                return;
                            }

                            $uploader = new Varien_File_Uploader('store_image');
                            $uploader->setAllowedExtensions(array('png','jpg','jpeg'));
                            $uploader->setAllowRenameFiles(false);
                            $uploader->setFilesDispersion(false);
                            $path = Mage::getBaseDir('media') . '/location' ;

                            if(!file_exists($path)) mkdir($path);

                            $newFileName = $_FILES['store_image']['name'];
                            $uploader->save($path, $newFileName);
                            $infoData['image_path'] = $newFileName;
                        }

                        $infoModel = Mage::getModel('location/info');
                        $infoModel->setData($infoData);

                        $locationModel->saveInfo($infoModel);
                        $write->commit();
                    }
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Location data saved successfully saved'));
                Mage::getSingleton('adminhtml/session')->setLocationData(false);

                $this->_redirect('*/*/');
                return;

            }catch (Exception $e){

                $write->rollback();
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setLocationData($this->getRequest()->getPost());

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
                $storeId = $this->getRequest()->getParam('id');

                $write = Mage::getSingleton('core/resource')->getConnection('core_write');
                $write->beginTransaction();

                $store = Mage::getModel('location/main')->load($storeId);
                $store->delete();

                $write->commit();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Store data deleted successfully saved'));

                $this->_redirect('*/*/');
                return;

            }catch (Exception $e){

                $write->rollback();

                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }

        $this->_redirect('*/*/');
    }
}