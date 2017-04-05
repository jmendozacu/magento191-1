<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Irfan
 * Date: 7/24/13
 * Time: 9:31 AM
 * To change this template use File | Settings | File Templates.
 */

class Netstarter_Cartbannerpromotion_Adminhtml_ListController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('cartbannerpromotion');
    }

    protected function init(){
        $this->_title($this->__('Manage Cart Promotion'))->_title($this->__('Manage Cart Promotion'));

        $this->loadLayout();
        $this->_setActiveMenu('cartbannerpromotion/cartbannerpromotion');
        $this->getLayout()->getBlock('head')->addItem('js_css', 'prototype/windows/themes/default.css');
        $this->getLayout()->getBlock('head')->addCss('lib/prototype/windows/themes/magento.css');
    }

    public function indexAction(){

        try {
            $this->init();
            $this->_addBreadcrumb(Mage::helper('cartbannerpromotion')->__('Manage Cart Prmotion'), Mage::helper('cartbannerpromotion')->__('Manage Cart Prmotion'));
            $this->_addContent($this->getLayout()->createBlock('cartbannerpromotion/adminhtml_promotionlist'));
            $this->renderLayout();

        } catch (Exception $e) {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_ROUTE_PAGE);
                if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
                    $this->_forward('defaultNoRoute');
                }
            return ;
        }
    }
    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->init();
        $id = $this->getRequest()->getParam('id', null);
        $model = Mage::getModel('cartbannerpromotion/promotionlist')->load($id);
        if ($id == 0 || $model->getPromotionId()) {
            Mage::register('cartbannerpromotion_data', $model);
            $this->_setActiveMenu('cartbannerpromotion/cartbannerpromotion');
            $this->_addBreadcrumb(Mage::helper('cartbannerpromotion')->__('Cart Promotion'), Mage::helper('cartbannerpromotion')->__('Promotion'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->getLayout()->getBlock('head')->addItem('js', 'admin_popup.js');

            $block = $this->getLayout()->createBlock(
                'Netstarter_Cartbannerpromotion_Block_Adminhtml_Promotionlist_Edit',
                'cartbannerpromotion'
            );
            $this->getLayout()->getBlock('content')->append($block);
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cartpromotionlist')->__('Profile does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost())
        {
            $banner = null;
            $model = Mage::getModel('cartbannerpromotion/promotionlist');
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                $banner = $model->getBanner();
            }
            $model->setData($data);
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            try {
                if ($id) {
                    $model->setId($id);
                }
                if($model->getPromotionStart()){
                    $date = Mage::app()->getLocale()->date($model->getPromotionStart(), Zend_Date::DATE_SHORT);
                    $model->setPromotionStart($date->toString('YYYY-MM-dd HH:mm:ss'));
                }

                if($model->getPromotionEnd()){
                    $date = Mage::app()->getLocale()->date($model->getPromotionEnd(), Zend_Date::DATE_SHORT);
                    $model->setPromotionEnd($date->toString('YYYY-MM-dd HH:mm:ss'));
                }
                $model->save();
                $id = $model->getPromotionId();
                $bannerPath = '';

                if (isset($_FILES['banner']['name']) && file_exists($_FILES['banner']['tmp_name'])){

                    $fileSize= $_FILES['banner']['size'];

                    if ($fileSize > 500000) {

                        Mage::getSingleton("adminhtml/session")->addError(Mage::helper("cartpromotionlist")->__("Image is too big."));
                        $this->_redirect("*/*/edit", array("id" => $id));
                        return;
                    }

                    $uploader = new Varien_File_Uploader('banner');
                    $uploader->setAllowedExtensions(array('png','jpg','jpeg'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $path = Mage::getBaseDir('media') . '/cartbanner' ;

                    if(!file_exists($path)) mkdir($path);

                    $newFileName = $_FILES['banner']['name'];
                    $uploader->save($path, $newFileName);
                    $bannerPath = $newFileName;
                    if ($id) {
                        $model->setBanner($bannerPath);
                    }
                    $model->save();
                }else if($banner && !isset($data['banner']['delete'])){
                    $model->setBanner($banner);
                    $model->save();
                }else if(isset($data['banner']['delete'])){
                    $model->setBanner('');
                    $model->save();
                }

                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('cartbannerpromotion')->__('Error saving example'));
                }

                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('cartbannerpromotion')->__('Example was successfully saved.'));
                Mage::getSingleton('adminhtml/session')
                    ->setFormData(false);

                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }

            return;
        }
        Mage::getSingleton('adminhtml/session')
            ->addError(Mage::helper('cartbannerpromotion')->__('No data found to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('cartbannerpromotion/promotionlist');
                $model->setId($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('cartbannerpromotion')
                        ->__('The example has been deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find the example to delete.'));
        $this->_redirect('*/*/');
    }

    public function productgridAction(){
        $this->loadLayout('popup');
        $this->_addContent($this->getLayout()->createBlock('cartbannerpromotion/adminhtml_promotionlist_product_grid'));
        $block = $this->getLayout()->createBlock('core/text');
        $block->setText(
            '<script type="text/javascript">
                function setProduct(val1,val2,val3)
                {
                    var cname = val3;
                    this.parent.updateParentTwo("product_id",val1,"product_name",cname);

                }
            </script>'
        );
        $this->getLayout()->getBlock('js')->append($block);

        $this->renderLayout();
    }
}