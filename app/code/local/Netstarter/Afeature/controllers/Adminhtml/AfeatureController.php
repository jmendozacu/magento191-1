<?php

class Netstarter_Afeature_Adminhtml_AfeatureController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('afeature/items')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
        return $this;
    }

    public function indexAction() {
        $this->_initAction();
        //$this->_addContent($this->getLayout()->createBlock('afeature/adminhtml_afeature'));
        $this->renderLayout();
    }

    public function editAction()
    {
        $afeatureId     = $this->getRequest()->getParam('id');
        $afeatureModel  = Mage::getModel('afeature/afeature')->load($afeatureId);

        if ($afeatureModel->getId() || $afeatureId == 0) {

            Mage::register('afeature_data', $afeatureModel);

            $this->loadLayout();
            $this->_setActiveMenu('afeature/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Afeature'), Mage::helper('adminhtml')->__('Item Afeature'));

            $this->getLayout()->getBlock('head');
            $this->_addContent($this->getLayout()->createBlock('afeature/adminhtml_afeature_edit'))
                 ->_addLeft($this->getLayout()->createBlock('afeature/adminhtml_afeature_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('afeature')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
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

                $afeatureModel = Mage::getModel('afeature/afeature');
              
                if (isset($_FILES['fileinputname']['name']) && file_exists($_FILES['fileinputname']['tmp_name'])){

                    $filesize= $_FILES['fileinputname']['size'];

                    $mobile_filesize = $_FILES['mobile_fileinputname']['size'];

                    if ($filesize > 500000) {
                        Mage::getSingleton("adminhtml/session")->addError(Mage::helper("afeature")->__("Image is too big."));
			            $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                        return;
                    } else if($mobile_filesize > 500000) {
                        Mage::getSingleton("adminhtml/session")->addError(Mage::helper("afeature")->__("Mobile Image is too big."));
                        $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                        return;
                    }

                    $uploader = new Varien_File_Uploader('fileinputname');
                    $uploader->setAllowedExtensions(array('png','jpg','jpeg'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $path = Mage::getBaseDir('media') . '/afeature' . DS . 'main' . DS ;
                    $extension = pathinfo($_FILES['fileinputname']['name'], PATHINFO_EXTENSION);
                    $newFileName = uniqid().'.'.$extension;
                    $uploader->save($path, $newFileName);
                    $data['fileinputname'] = $newFileName;


                   
                }else{
                    if (isset($data['fileinputname']['delete']) && $data['fileinputname']['delete'] == 1){
                        $data['image_main'] = '';
                    }
                }


                // mobile image upload
                if (isset($_FILES['mobile_fileinputname']['name']) && file_exists($_FILES['mobile_fileinputname']['tmp_name'])){
                    $mobile_uploader = new Varien_File_Uploader('mobile_fileinputname');
                    $mobile_uploader->setAllowedExtensions(array('png','jpg','jpeg'));
                    $mobile_uploader->setAllowRenameFiles(false);
                    $mobile_uploader->setFilesDispersion(false);
                    $path = Mage::getBaseDir('media') . '/afeature' . DS . 'mobile' . DS ;
                    $extension = pathinfo($_FILES['mobile_fileinputname']['name'], PATHINFO_EXTENSION);
                    $newFileName = uniqid().'.'.$extension;
                    $mobile_uploader->save($path, $newFileName);
                    $data['mobile_fileinputname'] = $newFileName;
                }

                //If Hex Color Code is entered without '#', append it
                $bgColor = $this->getRequest()->getParam('bg_color');
                if (strpos($bgColor, '#') === false) {
                    $bgColor = '#'.$bgColor;
                }


                $afeatureModel->setId($this->getRequest()->getParam('id'))
                    ->setTitle($this->getRequest()->getParam('title'))
                    ->setShortDesc($this->getRequest()->getParam('short_desc', ''))
                    ->setLongDesc($this->getRequest()->getParam('long_desc', ''))
                    ->setHasText($this->getRequest()->getParam('has_text',''))
                    ->setTextPosition($this->getRequest()->getParam('text_position'))
                    ->setlinkText($this->getRequest()->getParam('link_text'))
                    ->setAlt($this->getRequest()->getParam('tagline'))
                    ->setBgColor($bgColor)
                    ->setActive($this->getRequest()->getParam('active'))
                    ->setIsHidden($this->getRequest()->getParam('is_hidden'))
                    ->setUrl($this->getRequest()->getParam('url'));


                if (isset($data['fileinputname'])){

                    $afeatureModel->setImageUrl($data['fileinputname']);
                }

                if(isset($data['mobile_fileinputname'])) {
                    $afeatureModel->setMobileImageUrl($data['mobile_fileinputname']);
                }

                if (!$this->getRequest()->getParam('id')){

                    $afeatureModel->setDateCreated(now());
                }
                
		        $afeatureModel->setDateModified(now());
                $afeatureModel->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setAfeatureData(false);

                $this->_redirect('*/*/');
                return;

        }catch (Exception $e){

                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setAfeatureData($this->getRequest()->getPost());

                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }

        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0){

            try{

                $afeatureModel = Mage::getModel('afeature/afeature')->setId($this->getRequest()->getParam('id'))
                                ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            }
            catch (Exception $e){

                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * Product grid for AJAX request.
     * Sort and filter result for example.
     */

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
               $this->getLayout()->createBlock('importedit/adminhtml_afeature_grid')->toHtml()
        );
    }
}