<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 8/1/13
 * Time: 9:57 AM
 * Multiple CMS static or page content extractor with BLOCK caching
 *
 * ***** USAGE *****************
 *
 * <block type="afeature/static_cms" name="product_statics_block" template="afeature/static/static_block.phtml">
        <action method="addStaticIds"><id>product_view_shipping</id></action>
        <action method="addStaticIds"><id>product_view_returns</id></action>
        <action method="setCacheKey"><id>product_statics_block</id></action>
    </block>
 */
class Netstarter_Afeature_Block_Static_Cms extends Mage_Core_Block_Template
{

    protected $_staticIds = array();
    protected $_linkParameters = '';


    public function addStaticIds($id, $param=array())
    {
        if($id){
            $this->_staticIds[$id] = $param;
        }
    }

    public function setCacheKey($key)
    {

        $this->addData(array(
            'cache_lifetime'=> false,
            'cache_tags'    => array(Mage_Core_Model_Store::CACHE_TAG, Mage_Cms_Model_Block::CACHE_TAG),
            'cache_key'     => 'CACHE_ID_'.$key.'_STORE_'.Mage::app()->getStore()->getId()
        ));
    }

    /**
     *
     * @return array|null|string
     */
    public function staticBlocks()
    {
        $staticContents = array();

        foreach($this->_staticIds as $blockId => $blockParams){
            $content =  array('title' => null,'detail' => null);

            $block = Mage::getModel('cms/block')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($blockId);

            if ($block->getIsActive()) {

                $helper = Mage::helper('cms');
                $processor = $helper->getBlockTemplateProcessor();
                $html = $processor->filter($block->getContent());

                $content['detail'] = $html;
                $content['title'] = $block->getTitle();
                $content['params'] = $blockParams;
            }

            $staticContents[$blockId] = $content;
        }

        return $staticContents;
    }

    public function staticPages()
    {
        $staticContents = array();
        $storeId = Mage::app()->getStore()->getId();

        foreach($this->_staticIds as $pageId => $blockParams){
            $content =  array('title' => null,'url' => null);

            $page = Mage::getModel('cms/page')
                ->setStoreId($storeId)
                ->load($pageId, 'identifier');

            if ($page->getIsActive()) {

                $content['title'] = $page->getTitle();
                $content['url'] = Mage::Helper('cms/page')->getPageUrl($page->getPageId());
                $content['params'] = $blockParams;

                if($page->getWebsiteRoot()){

                    $pageNode = Mage::getResourceModel('enterprise_cms/hierarchy_node_collection')
                                        ->addFieldToFilter('page_id',$page->getPageId())
                                        ->addStoreFilter($storeId)
                                        ->getFirstItem();

                    if($pageNode->getNodeId())
                        $content['url'] = $pageNode->getUrl();
                }
            }

            $staticContents[$pageId] = $content;
        }

        return $staticContents;
    }

}