<?php
/**
 * Top menu block
 * extend Mage_Page_Block_Html_Topmenu *
 */

class Netstarter_Menu_Block_Html_Topmenumobile extends Mage_Core_Block_Template
{
     /**
     * Init top menu tree structure
     */
    public function _construct()
    {
        $this->addData(array(
            'cache_lifetime' => false,
            'cache_tags'    => array(Mage_Core_Model_Store::CACHE_TAG, Mage_Cms_Model_Block::CACHE_TAG)
        ));
    }

    public function getMobileCatList()
    {
        $_persistCats = Mage::registry('persist_cats');
        if(!empty($_persistCats)){

            $_categories = $_persistCats;
        }else{
            // get parent categories to generate Top Navigation for mobile version.
            $_categories = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('url_path')
                ->addAttributeToSelect('custom_link_url')
                ->addAttributeToFilter('include_in_menu', 1)
                ->addAttributeToFilter('level',2)
                ->addAttributeToFilter('is_active',array('eq'=>true))->load();
        }

        return $_categories;
    }
}
