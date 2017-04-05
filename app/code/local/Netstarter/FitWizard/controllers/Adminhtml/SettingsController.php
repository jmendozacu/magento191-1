<?php
/**
 * Class Netstarter_FitWizard_Adminhtml_SettingsController
 * @author  http://www.netstarter.com.au
 */
class Netstarter_FitWizard_Adminhtml_SettingsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }


    /**
     * Save Attribute Images
     */
    public function saveAction()
    {
        try{
            $path = Mage::getBaseDir('media') . DS . Netstarter_FitWizard_Helper_Data::MEDIA_FOLDER . DS;

            $fileArray = $_FILES;
            if ($fileArray && is_array($fileArray)) {
                foreach ($fileArray as $optionValueId => $file) {
                    if (isset($file['error']) && $file['error'] === 0) {
                        $uploader = new Varien_File_Uploader($optionValueId);
                        $uploader->setAllowedExtensions(array('csv'));
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $destFileName = $file['name'];
                        $uploader->save($path, $destFileName);
                    }
                }


                if (!empty($destFileName)) {
                    $model = Mage::getModel('fitwizard/combination');
                    $model->processLogicFile($path.$destFileName);
                    $file = Mage::helper('fitwizard')->saveLogicFileName($destFileName);



                    Mage::getSingleton('adminhtml/session')->addSuccess('File Successfully Uploaded');
                    Mage::getSingleton('core/session')->setImageUpload('done');
                }

            }

        }catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    public function clearAction ()
    {

        if($this->getRequest()->isPost()){
            $model = Mage::getModel('fitwizard/combination');
            if($model->clearAllBackupRecords()) {
                Mage::getSingleton('adminhtml/session')->addSuccess('Backup rows cleared successfully');
                Mage::getSingleton('core/session')->setImageUpload('done');
            } else {
                Mage::getSingleton('adminhtml/session')->addError('No data cleared');
                Mage::getSingleton('core/session')->setImageUpload('done');
            }
        }
        $this->_redirect('*/*/');
    }
}