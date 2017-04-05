<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gayan
 * Date: 7/16/13
 * Time: 11:08 AM
 * To change this template use File | Settings | File Templates. *
 *
 */
class Netstarter_Seo_Block_Page_Html_Head extends Mage_Page_Block_Html_Head{

    /*
     * function getRobots() already duplicated in Petbarn_CacheBuster_Block_Html_Head
     * because Petbarn_CacheBuster_Block_Html_Head has rewrite the html_head
     * so Netstarter_Seo cannot rewrite it until Petbarn_CacheBuster is disabled.
     * Or have to depend on Petbarn_CacheBuster, but it is not good.
     *
     * at the moment function getRobots() is running from Petbarn_CacheBuster_Block_Html_Head
     * I have duplicated it here to work, if that module is disabled.
     *
     * */
    public function getRobots()
    {
        if (($_product = Mage::registry('current_product')) &&
            ($robots = $_product->getAttributeText('robot_tags')) && Mage::helper('core')->isModuleEnabled('Netstarter_Seo')) {

            if($robots=="No"){
                return str_replace(',', ', ', parent::getRobots()); // to add a space after ','
            }
            $this->_data['robots'] = $robots;

        }

        else if (($_category = Mage::registry('current_category')) &&
            ($robots = $_category->getResource()->getAttribute('robot_tags')
                ->getFrontend()->getValue($_category)) && Mage::helper('core')->isModuleEnabled('Netstarter_Seo')) {


            if($robots=="No"){
                return str_replace(',', ', ', parent::getRobots()); // to add a space after ','
            }
            $this->_data['robots'] = $robots;
        }

        if (Mage::getSingleton('cms/page')->getPageId() && Mage::helper('core')->isModuleEnabled('Netstarter_Seo')) {
            $seoCmsPage = Mage::getModel('netstarter_seo/seocms')->load(Mage::getSingleton('cms/page')->getPageId(), 'page_id');

            $this->_data['robots'] = $seoCmsPage->getRobotTags();
        }
        if(!empty($this->_data['robots'])){
            return $this->_data['robots'];
        }
        else{ /* default robots */
            return str_replace(',', ', ', parent::getRobots()); // to add a space after ','
        }
    }

    public function getCanonicalCmsUrl() {

    }

    public function getPageTitle()
    {
        $seoCmsCollection = Mage::getModel('netstarter_seo/seocms')->load(Mage::getSingleton('cms/page')->getPageId(), 'page_id');
        $cmsPageCollection = Mage::getSingleton('cms/page')->load(Mage::getSingleton('cms/page')->getPageId());

        if(!empty($seoCmsCollection['pagetitle']))
        {
            return $seoCmsCollection['pagetitle'];
        } else {
            return $cmsPageCollection['title'];
        }
    }
}