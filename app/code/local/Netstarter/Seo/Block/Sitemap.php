<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gayan
 * Date: 7/17/13
 * Time: 11:26 AM
 * To change this template use File | Settings | File Templates.
 */

class Netstarter_Seo_Block_Sitemap extends Mage_Core_Block_Template
{

    public function getCmsCollection()
    {
        $htmlSitemap = Mage::getStoreConfig('htmlsitemap/settings/html_sitemapenable');
        $cmsEnable = Mage::getStoreConfig('htmlsitemap/settings/cms_enable');
        if(!$htmlSitemap){
            return null;
        }
        if($cmsEnable){

            $tbl_cmspage = Mage::getSingleton('core/resource')->getTableName('cms/page');
            $tbl_cmspage_store = Mage::getSingleton('core/resource')->getTableName('cms/page_store');
            $tbl_seocms = Mage::getSingleton('core/resource')->getTableName('netstarter_seo/seocms');
            $storeId = Mage::app()->getStore()->getStoreId();

            $collection = Mage::getModel('enterprise_cms/hierarchy_node')
                ->getCollection();
            $collection
                ->getSelect()
                ->distinct(true)
                ->join(array('t2' => $tbl_cmspage), 'main_table.page_id = t2.page_id')
                ->join(array('t3' => $tbl_cmspage_store), 'main_table.page_id = t3.page_id')
                ->join(array('t4' => $tbl_seocms), 'main_table.page_id = t4.page_id')
                ->where('t2.is_active=1')
                //->where("main_table.request_url like 'header%' or main_table.request_url like 'footer%'")
                ->where('t4.show_in_sitemap=1')
                ->where('t3.store_id IN(?)', array(0, $storeId))
                ->order(new Zend_Db_Expr("main_table.xpath ASC"))

            ;

            $cmsPageArray = array();
            foreach($collection as $item){
                $cmsPageArray[] = array(
                        'identifier'    =>  $item->getIdentifier(),
                        'title' =>  $item->getTitle(),
                        'level' =>  $item->getLevel(),
                );
            }

            $cmsPageArray = array_map("unserialize", array_unique(array_map("serialize", $cmsPageArray)));


            return $cmsPageArray;
        }


    }

}