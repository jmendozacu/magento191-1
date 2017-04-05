<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 8/12/13
 * Time: 1:23 PM
 *
 */ 
class Netstarter_Modulerewrites_Model_Container_Breadcrumbs extends Enterprise_PageCache_Model_Container_Breadcrumbs
{


    protected function _prepareCatalogBreadCrumb()
    {

        $breadcrumbsBlock = $this->_getPlaceHolderBlock();
        $breadcrumbsBlock->setNameInLayout('breadcrumbs');

        $breadcrumbsBlock->addCrumb('home', array(
            'label'=>Mage::helper('catalog')->__('Home'),
            'title'=>Mage::helper('catalog')->__('Go to Home Page'),
            'link'=>Mage::getBaseUrl()
        ));

        $title = array();
        $path  = Mage::helper('catalog')->getBreadcrumbPath();

        foreach ($path as $name => $breadcrumb) {
            $breadcrumbsBlock->addCrumb($name, $breadcrumb);
            $title[] = $breadcrumb['label'];
        }

        return $breadcrumbsBlock;

    }

    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false;
    }
}
