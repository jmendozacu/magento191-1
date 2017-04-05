<?php
/**
 * Class OptionsController
 * adminhtml controller for option image management
 * @author bzhang@netstarter.com.au
 */
class Netstarter_Colors_Adminhtml_OptionController extends Mage_Adminhtml_Controller_Action
{
    //product entity type id
    protected $_entityTypeId;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_entityTypeId = Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY)->getTypeId();
    }
    
    protected function _initAction()
    {
        $this->_title($this->__('Nestarter Colors'))
             ->_title($this->__('Filter Options'))
             ->_title($this->__('Manage Filter Options'));
        $this->loadLayout()
             ->_setActiveMenu('netstarter_colors/filter_option')
             ->_addBreadcrumb(Mage::helper('colors')->__('Netstarter Colors'), Mage::helper('colors')->__('Netstarter Colors'))
             ->_addBreadcrumb(Mage::helper('colors')->__('Manage Filter Option'), Mage::helper('colors')->__('Manage Filter Option'))
        ;
        return $this;
    }
    
    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('colors/adminhtml_option'))
            ->renderLayout();
    }
    
    public function saveAction(){
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('colors')->__('Color options has been saved.'));
        Mage::getSingleton('adminhtml/session')->setFormData(false);
        $this->_redirect('*/*/edit');
    }
    
    public function editAction() {
        $attribute=Mage::getModel('catalog/product')->getResource()->getAttribute("color");
        $id = $attribute->getId();
        $model = Mage::getModel('catalog/resource_eav_attribute')->setEntityTypeId($this->_entityTypeId);;
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('catalog')->__('This attribute no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
            Mage::register('attribute_model', $model);
        }
        $this->_initAction();
        $this->getLayout()->getBlock('head')
                ->addJs('jscolor/jscolor.js')
                ->addJs('ns/uploader/fileuploader.js')
                ->addJs('ns/uploader/ajax-functions.js')
                ->addItem('js_css', 'ns/uploader/fileuploader.css');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Filter'), Mage::helper('adminhtml')->__('Filter ' . $model->getName() . ' Management'));
        $this->_addContent($this->getLayout()->createBlock('colors/adminhtml_option_edit'))
                ->_addLeft($this->getLayout()->createBlock('colors/adminhtml_option_edit_tabs'));
        $this->renderLayout();
    }
    
    public function colorAction(){
        $opt = $this->getRequest()->getParam('option_id', null);
        $code = $this->getRequest()->getParam('color_code', null);
        if ($opt === null || $code === null || !is_numeric($opt)|| $code == '') {
            $result = array('error' => 'Missing option id or color code');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        } elseif(!preg_match('/^[0-9a-zA-Z]{6}$/', $code)){
            $result = array('error' => 'Incorrect color code, it must be a validte color code like FFFFFF ');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        } else {
            $model = Mage::getModel('colors/filter_option')->loadByOptionId($opt);

            if($model->getId()){
                $model->setData('color_code',$code);
                $model->save();
            }else{
                $model->setData('color_code',$code);
                $model->setData('store_id',0);
                $model->setData('option_id',$opt);
                $model->setId(null);
                $model->save();
            }
            $result = array('success' => 'color code updated');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }
    }

    public function uploadAction() {
        $result = array();
        $field = $this->getRequest()->getParam('field_id',null);
        $opt = $this->getRequest()->getParam('option_id',null);
        $error = false;
        if ($opt === null || $field === null || !is_numeric($opt)|| $field == '') {
            $result = array('error' => 'Missing option code or field name', 'errorcode' => '0');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }
        try {
            $model = Mage::getModel('colors/filter_option')->loadByOptionId($opt);
            if ($model->getId()) {
                $location = Mage::getBaseDir('media') . DS . 'color_palette' . DS . $model->getImageIcon();
                if (file_exists($location)) unlink($location);
            }
            $uploader = new Varien_File_Uploader('qqfile');
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
            $uploader->addValidateCallback('catalog_product_image', Mage::helper('catalog/image'), 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $result = $uploader->save(Mage::getBaseDir('media') . '/color_palette/');
            $result['url'] = Mage::getBaseUrl('media') . 'color_palette/' . $result['file'];
            $result['file'] = $result['file'];
            $result['cookie'] = array(
                'name' => session_name(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain()
            );
            //save to table
            $model->setData($field,$result['file']);
            $model->setData('option_id',$opt);
            $model->save();
        } catch (Exception $e) {
            $error = true;
            $result = array('error' => $e->getMessage(), 'errorcode' => $e->getCode());
        }
        if (!$error) {
            unset($result['error']);
            $result['success'] = 1;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function imgdeleteAction() {
        $opt = $this->getRequest()->getParam('option_id', null);
        $field = $this->getRequest()->getParam('field_id', null);
        if ($opt === null || $field === null || !is_numeric($opt)|| $field == '') {
            $result = array('error' => 'Missing option id or field name');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        } else {
            $model = Mage::getModel('colors/filter_option')->loadByOptionId($opt);
            if ($model->getId()) {
                $location = Mage::getBaseDir('media') . DS . 'color_palette' . DS . $model->getData($field);
                if (file_exists($location)) unlink($location);
                $model->setData($field,null);
                $model->save();
            }
            $result = array('success' => 'file deleted');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }
    }
    
}
