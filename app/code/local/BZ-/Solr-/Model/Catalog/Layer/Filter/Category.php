<?php

class BZ_Solr_Model_Catalog_Layer_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category
{
    /**
     * Get data array for building category filter items
     * @return array
     */
    protected function _getItemsData()
    {
        $key    = $this->getLayer()->getStateKey() . '_SUBCATEGORIES';
        $data   = $this->getLayer()->getCacheData($key);

        if ($data === null) {
            /** @var $category Mage_Catalog_Model_Categeory */
            $category   = $this->getCategory();
            $categories = $category->getChildrenCategories();

            $productCollection = $this->getLayer()->getProductCollection();
            $facets = $productCollection->getFacetedData('category_ids');

            $data = array();
            foreach ($categories as $category) {
                $categoryId = $category->getId();
                if (isset($facets[$categoryId])) {
                    $category->setProductCount($facets[$categoryId]);
                } else {
                    $category->setProductCount(0);
                }

                if ($category->getIsActive() && $category->getProductCount()) {
                    $data[] = array(
                        'label' => Mage::helper('core')->escapeHtml($category->getName()),
                        'value' => $categoryId,
                        'count' => $category->getProductCount(),
                    );
                }
            }

            $tags = $this->getLayer()->getStateTags();
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }

        return $data;
    }

    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $helper = Mage::helper('bz_solr/navigation');
        $filter = $request->getParam($this->getRequestVar());
        if(!$filter){
            $name = $filterBlock->getName();
            $clean_name = $helper->labelEncode($name);
            $filter = $request->getParam($clean_name);
        }

        if (!$filter) {
            return $this;
        }
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

        if ($this->_isValidCategory($this->_appliedCategory)) {
            $this->getLayer()->getProductCollection()
                ->addCategoryFilter($this->_appliedCategory);

            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_appliedCategory->getName(), $filter)
            );
        }

        return $this;
    }
    
    /**
     * Add params to faceted search
     */
    public function addFacetCondition()
    {
        $category = $this->getCategory();
        $childrenCategories = $category->getChildrenCategories();
        $useFlat = (bool) Mage::getStoreConfig('catalog/frontend/flat_catalog_category');
        $categories = ($useFlat)
            ? array_keys($childrenCategories)
            : array_keys($childrenCategories->toArray());
        $this->getLayer()->getProductCollection()->setFacetCondition('category_ids', $categories);
        return $this;
    }
}
