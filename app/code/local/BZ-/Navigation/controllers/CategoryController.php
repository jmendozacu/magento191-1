<?php

/**
 * Class CategoryController
 * Used for AJAX calls
 * @author bzhang@netstarter.com.au
 */
require_once 'Mage/Catalog/controllers/CategoryController.php';

class BZ_Navigation_CategoryController extends Mage_Catalog_CategoryController
{
    /**
     * Category view action here we just return layer and content
     */
    public function viewAction()
    {
        $isAjax = $this->getRequest()->isAjax();
        //load cache
        if ($category = $this->_initCatagory()) {
            $design = Mage::getSingleton('catalog/design');
            $settings = $design->getDesignSettings($category);

            // apply custom design
            if ($settings->getCustomDesign()) {
                $design->applyCustomDesign($settings->getCustomDesign());
            }

            Mage::getSingleton('catalog/session')->setLastViewedCategoryId($category->getId());

            $update = $this->getLayout()->getUpdate();
            $update->addHandle('default');

            if (!$category->hasChildren()) {
                $update->addHandle('catalog_category_layered_nochildren');
            }

            $this->addActionLayoutHandles();
            $update->addHandle($category->getLayoutUpdateHandle());
            $update->addHandle('CATEGORY_' . $category->getId());
            $this->loadLayoutUpdates();

            // apply custom layout update once layout is loaded
            if ($layoutUpdates = $settings->getLayoutUpdates()) {
                if (is_array($layoutUpdates)) {
                    foreach($layoutUpdates as $layoutUpdate) {
                        $update->addUpdate($layoutUpdate);
                    }
                }
            }

            $this->generateLayoutXml()->generateLayoutBlocks();
            // apply custom layout (page) template once the blocks are generated
            if ($settings->getPageLayout()) {
                $this->getLayout()->helper('page/layout')->applyTemplate($settings->getPageLayout());
            }
            
            if($isAjax){
                $this->getLayout()->getOutput(); //must run this to prevent layout update on the fly
                $this->_initLayoutMessages('catalog/session');
                $this->_initLayoutMessages('checkout/session');
                Mage::getSingleton('catalog/session')->setParamsMemorizeDisabled(0);
                $left = $this->getLayout()->getBlock('left')->toHtml();
                $right = $this->getLayout()->getBlock('right')->toHtml();
                $content = $this->getLayout()->getBlock('content')->toHtml();
                $head = $this->getLayout()->getBlock('head');
                $title = $head->getTitle();
                $meta_description =  $head->getDescription();
                $meta_keyword = $head->getKeywords();
                $robots = $head->getRobots();
                $meta_data = array('robots'=>$robots,'title'=>$title,'description'=>$meta_description,'keywords'=>$meta_keyword);
                $ajax = array('left'=>$left,'right'=>$right, 'content'=>$content, 'redirect'=>0, 'meta_data'=>$meta_data);
                $this->getResponse()->setBody(json_encode($ajax));
            }else{
                //Mage::getSingleton('catalog/session')->setData('isajax',false);
                //default controller
                if ($root = $this->getLayout()->getBlock('root')) {
                $root->addBodyClass('categorypath-' . $category->getUrlPath())
                    ->addBodyClass('category-' . $category->getUrlKey());
                }
                $this->_initLayoutMessages('catalog/session');
                $this->_initLayoutMessages('checkout/session');
                Mage::getSingleton('catalog/session')->setParamsMemorizeDisabled(0);
                $this->renderLayout();
            }
        }
    }

}