<?php
/**
 * Class Observer
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Solr_Model_Observer
{

    public function eavAttributeEditFormInit(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('bz_solr')->isThirdPartyEngineAvailable()) {
            return;
        }

        $form      = $observer->getEvent()->getForm();
        $attribute = $observer->getEvent()->getAttribute();
        $fieldset  = $form->getElement('front_fieldset');

        $fieldset->addField('search_weight', 'select', array(
            'name'        => 'search_weight',
            'label'       => Mage::helper('catalog')->__('Search Weight'),
            'values'      => Mage::getModel('bz_solr/weight')->getOptions(),
        ), 'is_searchable');
        /**
         * Disable default search fields
         */
        $attributeCode = $attribute->getAttributeCode();

        if ($attributeCode == 'name') {
            $form->getElement('is_searchable')->setDisabled(1);
        }
    }

    /**
     * Invalidate catalog search index after creating of new customer group or changing tax class of existing,
     * because there are all combinations of customer groups and websites per price stored at search engine index
     * and there will be no document's price field for customers that belong to new group or data will be not actual.
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerGroupSaveAfter(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('bz_solr')->isThirdPartyEngineAvailable()) {
            return;
        }

        $object = $observer->getEvent()->getDataObject();
        if ($object->isObjectNew() || $object->getTaxClassId() != $object->getOrigData('tax_class_id')) {
            Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_fulltext')
                ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }
    }

    /**
     * Hold commit at indexation start if needed
     *
     * @param Varien_Event_Observer $observer
     */
    public function holdCommit(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('bz_solr')->isThirdPartyEngineAvailable()) {
            return;
        }

        $engine = Mage::helper('catalogsearch')->getEngine();
        if (!$engine->holdCommit()) {
            return;
        }
        /*
         * Index needs to be optimized if all products were affected
         */
        $productIds = $observer->getEvent()->getProductIds();
        if (is_null($productIds)) {
            $engine->setIndexNeedsOptimization();
        }
    }

    /**
     * Apply changes in search engine index.
     * Make index optimization if documents were added to index.
     * Allow commit if it was held.
     *
     * @param Varien_Event_Observer $observer
     */
    public function applyIndexChanges(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('bz_solr')->isThirdPartyEngineAvailable()) {
            return;
        }

        $engine = Mage::helper('catalogsearch')->getEngine();
        if (!$engine->allowCommit()) {
            return;
        }

        if ($engine->getIndexNeedsOptimization()) {
            $engine->optimizeIndex();
        } else {
            $engine->commitChanges();
        }

        /**
         * Cleaning MAXPRICE cache
         */
        $cacheTag = Mage::getSingleton('bz_solr/catalog_layer_filter_price')->getCacheTag();
        Mage::app()->cleanCache(array($cacheTag));
        //CE1.18 use the following
        /*
        Mage::dispatchEvent('clean_cache_by_tags', array('tags' => array(
            $cacheTag
        )));
        */
    }

    /**
     * Store searchable attributes at adapter to avoid new collection load there
     *
     * @param Varien_Event_Observer $observer
     */
    public function storeSearchableAttributes(Varien_Event_Observer $observer)
    {
        $engine     = $observer->getEvent()->getEngine();
        $attributes = $observer->getEvent()->getAttributes();
        if (!$engine || !$attributes || !Mage::helper('bz_solr')->isThirdPartyEngineAvailable()) {
            return;
        }

        foreach ($attributes as $attribute) {
            if (!$attribute->usesSource()) {
                continue;
            }

            $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($attribute->getAttributeId())
                ->setPositionOrder(Varien_Db_Select::SQL_ASC, true)
                ->load();

            $optionsOrder = array();
            foreach ($optionCollection as $option) {
                $optionsOrder[] = $option->getOptionId();
            }
            $optionsOrder = array_flip($optionsOrder);

            $attribute->setOptionsOrder($optionsOrder);
        }

        $engine->storeSearchableAttributes($attributes);
    }

    /**
     * Save store ids for website or store group before deleting
     * because lazy load for this property is used and this info is unavailable after deletion
     *
     * @param Varien_Event_Observer $observer
     */
    public function saveStoreIdsBeforeScopeDelete(Varien_Event_Observer $observer)
    {
        $object = $observer->getEvent()->getDataObject();
        $object->getStoreIds();
    }

    /**
     * Clear index data for deleted stores
     *
     * @param Varien_Event_Observer $observer
     */
    public function clearIndexForStores(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('bz_solr')->isThirdPartyEngineAvailable()) {
            return;
        }

        $object = $observer->getEvent()->getDataObject();
        if ($object instanceof Mage_Core_Model_Website
            || $object instanceof Mage_Core_Model_Store_Group
        ) {
            $storeIds = $object->getStoreIds();
        } elseif ($object instanceof Mage_Core_Model_Store) {
            $storeIds = $object->getId();
        } else {
            $storeIds = array();
        }

        if (!empty($storeIds)) {
            $engine = Mage::helper('catalogsearch')->getEngine();
            $engine->cleanIndex($storeIds);
        }
    }

    /**
     * Reset search engine if it is enabled for catalog navigation
     *
     * @param Varien_Event_Observer $observer
     */
    public function resetCurrentCatalogLayer(Varien_Event_Observer $observer)
    {
        if (Mage::helper('bz_solr')->getIsEngineAvailableForNavigation()) {
            Mage::register('current_layer', Mage::getSingleton('bz_solr/catalog_layer'));
        }
    }

    /**
     * Reset search engine if it is enabled for search navigation
     *
     * @param Varien_Event_Observer $observer
     */
    public function resetCurrentSearchLayer(Varien_Event_Observer $observer)
    {
        if (Mage::helper('bz_solr')->getIsEngineAvailableForNavigation(false)) {
            Mage::register('current_layer', Mage::getSingleton('bz_solr/search_layer'));
        }
    }

    /**
     * Reindex data after price reindex
     *
     * @param Varien_Event_Observer $observer
     */
    public function runFulltextReindexAfterPriceReindex(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('bz_solr')->isThirdPartyEngineAvailable()) {
            return;
        }

        /* @var BZ_Solr_Model_Indexer_Indexer $indexer */
        $indexer = Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_fulltext');
        if (empty($indexer)) {
            return;
        }

        if ('process' == strtolower(Mage::app()->getRequest()->getControllerName())) {
            $indexer->reindexAll();
        } else {
            $indexer->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }
    }
    
    /**
     * index after cms page saved
     */
    public function cmsPageSaveCommitAfterIndex(Varien_Event_Observer $observer)
    {
        $obj = $observer->getEvent()->getDataObject();
        if($obj instanceof Mage_CMS_Model_Page || $obj::CACHE_TAG == 'cms_page'){
            Mage::getSingleton('index/indexer')->processEntityAction(
                $obj, $obj::CACHE_TAG, Mage_Index_Model_Event::TYPE_SAVE
            );
        }
    }
    /**
     * index after cms page deleted
     */
    public function cmsPageDeleteCommitAfterIndex(Varien_Event_Observer $observer)
    {
        $obj = $observer->getEvent()->getDataObject();
        if($obj instanceof Mage_CMS_Model_Page || $obj::CACHE_TAG == 'cms_page'){
            Mage::getSingleton('index/indexer')->processEntityAction(
                $obj, $obj::CACHE_TAG, Mage_Index_Model_Event::TYPE_DELETE
            );
        }
    }
    
    /**
     * index after category saved
     */
    public function catalogCategorySaveCommitAfterIndex(Varien_Event_Observer $observer)
    {
        $obj = $observer->getEvent()->getDataObject();
        if($obj instanceof Mage_Catalog_Model_Category || $obj::ENTITY == 'catalog_category'){
            Mage::getSingleton('index/indexer')->processEntityAction(
                $obj, $obj::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
            );
        }
    }
    /**
     * index after category deleted
     */
    public function catalogCategoryDeleteCommitAfterIndex(Varien_Event_Observer $observer)
    {
        $obj = $observer->getEvent()->getDataObject();
        if($obj instanceof Mage_Catalog_Model_Category || $obj::ENTITY == 'catalog_category'){
            Mage::getSingleton('index/indexer')->processEntityAction(
                $obj, $obj::ENTITY, Mage_Index_Model_Event::TYPE_DELETE
            );
        }
    }

    /**
     * inject is_searchable field to the main tab cms page form
     * adminhtml_cms_page_edit_tab_main_prepare_form
     */
    public function cmsPageAddIsSearchable(Varien_Event_Observer $observer){
        $helper = Mage::helper('bz_solr');
        if ($helper->isThirdPartyEngineAvailable()){
            $form = $observer->getEvent()->getForm();
            $fieldset = $form->getElement('base_fieldset');
            $fieldset->addField('is_searchable', 'select', array(
                'label' => Mage::helper('bz_solr')->__('Is Searchable in Solr'),
                'title' => Mage::helper('bz_solr')->__('Is Searchable in Solr'),
                'name' => 'is_searchable',
                'required' => true,
                'options' => array(1 => 'Yes', 0 => 'No')
            ));
        }
    }
    
    /**
     * Retrieve Fulltext Search instance
     *
     * @return Mage_CatalogSearch_Model_Fulltext
     */
    protected function _getIndexer()
    {
        return Mage::getSingleton('catalogsearch/fulltext');
    }

    /**
     * Reindex data after catalog category/product partial reindex
     *
     * @param Varien_Event_Observer $observer
     */
    public function rebuiltIndex(Varien_Event_Observer $observer)
    {
        $this->_getIndexer()->rebuildIndex(null, $observer->getEvent()->getProductIds())->resetSearchResults();
    }

}
