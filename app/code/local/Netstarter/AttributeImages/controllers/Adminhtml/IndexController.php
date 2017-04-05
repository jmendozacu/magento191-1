<?php

class Netstarter_AttributeImages_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    protected $_mediaFolder = 'attributeimages';
    protected $_fileExtension = '.png';

    /**
     * indexAction
     */
    public function indexAction()
    {

        if(Mage::getSingleton('core/session')->getImageUpload())
        {
            Mage::getSingleton('core/session')->unsImageUpload();
            $this->loadLayout();
            $this->getLayout()->getBlock('head')->addJs('jquery/reload.js');
            $this->renderLayout();
        }else{
            Mage::unregister('imageUpload');
            $this->loadLayout();
            $this->renderLayout();

        }
    }

    /**
     * Save Attribute Images
     */
    public function saveAction()
    {
        try{
            $attributeCode = $this->getRequest()->getParam('attcode') ? $this->getRequest()->getParam('attcode') : 'features';
            $path = Mage::getBaseDir('media') . DS . $this->_mediaFolder . DS .$attributeCode. DS;

            $fileArray = $_FILES;
            if ($fileArray && is_array($fileArray)) {
                foreach ($fileArray as $optionValueId => $file) {
                    if (isset($file['error']) && $file['error'] === 0) {
                        $uploader = new Varien_File_Uploader($optionValueId);
                        $uploader->setAllowedExtensions(array('png'));
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $destFileName = $attributeCode.'_'.$optionValueId.$this->_fileExtension;
                        $uploader->save($path, $destFileName);
                    }
                }

                Mage::helper('attributeimages')->increaseImageCdnNumber();

                Mage::getSingleton('adminhtml/session')->addSuccess('Images Uploaded');
                Mage::getSingleton('core/session')->setImageUpload('done');

            }

        }catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}