<?php

/**
 * Class Tree
 *
 * @author Ben Zhang <ben_zhanghf@hotmail.com>
 */
class BZ_Navigation_Helper_Tree extends Mage_Core_Helper_Abstract
{
    protected $_items;
    protected $_currentFilterPath = array();
    protected $_catPath = array();

    protected function _addCollectionData($category_id, $tree)
    {
        $storeId = Mage::app()->getStore()->getId();
        $collection = Mage::getResourceModel('catalog/category_collection');
        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active')
            ->setProductStoreId($storeId)
            //->setLoadProductCount(true) this method takes about 1 second
            ->setStoreId($storeId)
            ->addAttributeToFilter('is_active', 1)
            ->addFieldToFilter('path',array('like'=>'%/'.$category_id.'/%'));
        $collection->load();

        foreach ($collection as $category) {
            if ($tree->getNodeById($category->getId())) {
                $tree->getNodeById($category->getId())
                    ->addData($category->getData());
            }
        }
        return $tree;
    }

    public function getTreeHtml($items, $currentFilterPath, $catPath, $showSubTree = false)
    {
        $treeLevel = 0;
        if($showSubTree) $treeLevel = 4;

        $parentCat = Mage::registry('parent_category');
        $tree = Mage::getResourceModel('catalog/category_tree');
        $tree->loadNode($parentCat)
            ->loadChildren($treeLevel)
            ->getChildren();
        $tree_collection = $this->_addCollectionData($parentCat, $tree);
        $root = $tree_collection->getNodeById($parentCat);
        $this->_items = $items;
        $this->_currentFilterPath = $currentFilterPath;
        $this->_catPath = explode('/', $catPath);
        return $this->_getHtml($root, $showSubTree);
    }

    public function _getHtml(Varien_Data_Tree_Node $menuTree, $showSubTree = false)
    {
        $html = '';
        $children = $menuTree->getChildren();

        foreach ($children as $child) {
            $id = $child->getId();
            //if(!isset($this->_items[$id])) continue;
            if(isset($this->_items[$id][0])){
                $url = $this->_items[$id][0];
            }else{
                continue;
            }
            $prd_count = (isset($this->_items[$id][1]))?$this->_items[$id][1]:0;
            if(!$prd_count) continue;

            $classes = array('bz-filter-category');
            if($child->hasChildren()) $classes[] = 'parent';
            if(in_array($id,$this->_catPath)) $classes[] = 'expanded';

            $html .= '<li class="' . implode(' ',$classes) . '">';
            if($url){
                $html .= '<a href="' . $url . '">'
                    . $child->getName() .' ('.$prd_count.')</a> <span class="arrow"></span>';
            }else{
                $html .= '<span>'.$child->getName() .' ('.$prd_count.')</span>';
            }

            $childId =  $child->getId();
            $showChild = true;

            //removed for SKIN-960 - Feb PDF
            /*if(!$showSubTree){
                $showChild = in_array($childId, $this->_catPath);
            }*/

            if ($showChild && $child->hasChildren()) {
                if(in_array($id,$this->_currentFilterPath)){
                    $html .= '<ul>';
                }else{
                    $html .= '<ul class = "brands-category2">';
                }
                $html .= $this->_getHtml($child, $showSubTree);
                $html .= '</ul>';
            }
            $html .= '</li>';
        }
        return $html;
    }
}