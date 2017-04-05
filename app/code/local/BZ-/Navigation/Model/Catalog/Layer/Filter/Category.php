<?php
/**
 * Class Category
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Model_Catalog_Layer_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category
{

    protected $_prepareUrl = false;
    /**
     * 
     * @param Zend_Controller_Request_Abstract $request
     * @param type $filterBlock
     * @return NS_Navigation_Model_Catalog_Layer_Filter_Category
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = $request->getParam($this->getRequestVar());
        $helper = Mage::helper('bz_navigation');

        $showSubTree = false;
        $currentCategory = $this->getCategory();
        $brandId = Mage::getStoreConfig('catalog/frontend/brands_category_id');
        $parentCatId = $currentCategory->getParentId();

        if($brandId == $parentCatId){
            $showSubTree = true;
        }else{
           // SKIN-960 - to display the back button and the menu
           if($currentCategory->getLevel() == 3){
               Mage::register('show_back',true);
           }
            if($currentCategory->getLevel() == 4){
                Mage::register('show_back',true);
            }

        }

        if(!$filter){
            $name = $filterBlock->getName();
            $clean_name = $helper->labelEncode($name);
            $filter = $request->getParam($clean_name);
        }

        if($filter){

            //single select for category as it is not allow multiple and we can not use the name to load category as they may have same name!
            //category should be /cat-name_id/
            $arr = explode('_',$filter);
            if(isset($arr[1]) && is_numeric($arr[1]) && $arr[1]>1){
                $filter = (int) $arr[1];
            }else{
                return $this;
            }

            $this->_categoryId = $filter;

            Mage::register('current_category_filter', $this->getCategory(), true);

            $this->_appliedCategory = Mage::getModel('catalog/category')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($filter);

            // SKIN-960 - to display the back button and the menu
            if($brandId == $parentCatId && $this->_appliedCategory->getLevel() == 3){
                Mage::register('show_back',true);
                $showSubTree = false;
            }


            if ($this->_isValidCategory($this->_appliedCategory)) {
                $this->getLayer()->getProductCollection()
                    ->filterMultipleCategories($this->_categoryId);

                $this->getLayer()->getState()->addFilter(
                    $this->_createItem($this->_appliedCategory->getName(), $filter)
                );
            }
            return $this;
        }

        Mage::register('show_sub_tree',$showSubTree);

        return $this;
    }


    public function getFilterPath()
    {
        if($this->_appliedCategory){
            return $this->_appliedCategory->getPathIds();
        }
        return array();
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $key = $this->getLayer()->getStateKey().'_SUBCATEGORIES';
        $data = $this->getLayer()->getAggregator()->getCacheData($key);
        $isRootCat = false;
        if ($data === null) {

            $currentCategory = $this->getLayer()->getCurrentCategory();

            if($currentCategory->getLevel() == 1){
                $categories = $currentCategory->getChildrenCategories();
                $this->getLayer()->getProductCollection()
                    ->addCountToCategories($categories);
                $isRootCat = true;
            }else{

                $brandCatId = Mage::getStoreConfig('catalog/frontend/brands_category_id', Mage::app()->getStore());
                $paths = $currentCategory->getPathIds();

                $firstLevelCats = Mage::getResourceModel('catalog/category_collection')
                    ->initCache(Mage::app()->getCache(), 'category', array(Mage_Catalog_Model_Category::CACHE_TAG))
                    ->addAttributeToSelect('entity_id')
                    ->addFieldToFilter('level', array('eq' => 2))->load();

                $firstLevelCatsAttr = array();

                foreach($firstLevelCats as $firstLevelCat){
                    $firstLevelCatsAttr[] = $firstLevelCat->getEntityId();
                }

                $parentCat = array_intersect($paths, $firstLevelCatsAttr);
                if($parentCat)
                    $parentCat = array_pop($parentCat);

                $removeFilterCategory = true;
                if($currentCategory->getParentId() == $brandCatId){

                    $this->_prepareUrl = true;
                    $removeFilterCategory = false;

                    $rootCatId = Mage::app()->getStore()->getRootCategoryId();
                    $parentCat = $rootCatId;
                    $categories = $currentCategory->getCategories($parentCat, 0, false, true, true);
                    $categories->removeItemByKey($brandCatId);
                }else{
                    $categories = $currentCategory->getCategories($parentCat, 0, false, true, true);
                }


                $this->getLayer()->getProductCollection()->getChildCounts($categories, $removeFilterCategory);

                Mage::register('parent_category', $parentCat, false);
                Mage::register('cat_title',$currentCategory->getName());
            }

            Mage::register('is_root_cat',$isRootCat , false);

            $data = array();
            foreach ($categories as $category) {

                if ($category->getIsActive() && $category->getProductCount()) {
                    $data[] = array(
                        'label' => Mage::helper('core')->htmlEscape($category->getName()),
                        'value' => $category->getId(),
                        'count' => $category->getProductCount(),
                        'category_url' => $category->getUrl(),
                    );
                }
            }

            $tags = $this->getLayer()->getStateTags();
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }

    protected function _createItem($label, $value, $count=0,$url='')
    {
        return Mage::getModel('bz_navigation/catalog_layer_filter_item')
            ->setFilter($this)
            ->setLabel($label)
            ->setValue($value)
            ->setCategoryUrl($url)
            ->setPrapareUrl($this->_prepareUrl)
            ->setCount($count);
    }

    protected function _initItems()
    {
        $data = $this->_getItemsData();
        $items=array();
        foreach ($data as $itemData) {
            $items[] = $this->_createItem(
                $itemData['label'],
                $itemData['value'],
                $itemData['count'],
                $itemData['category_url']
            );
        }
        $this->_items = $items;
        return $this;
    }
}