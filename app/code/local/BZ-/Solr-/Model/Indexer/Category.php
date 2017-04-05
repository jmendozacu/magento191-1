<?php
/**
 * Class Category
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Solr_Model_Indexer_Category extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'bz_solr_category_fulltext_match_result';
    
    protected $_matchedEntities = array(
        Mage_Catalog_Model_Category::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE
        )
    );
    
    protected function _construct() {
        //$this->_init('bz_solr/indexer_category');
        $helper = Mage::helper('bz_solr');
        if ($helper->isThirdPartyEngineAvailable()) $this->_isVisible = true;
        else $this->_isVisible = false;
    }
    
    public function getName()
    {
        return 'Solr Category Search Index';
    }
    
    public function getDescription()
    {
        return 'Rebuild Solr Category Search Index';
    }
    
    protected function _getIndexer()
    {
        return Mage::getResourceSingleton('bz_solr/indexer_category');
    }
    
    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (!empty($data['bz_solr_category_reindex_all'])) {
            $this->reindexAll();
        } else if (!empty($data['bz_solr_category_delete_id'])) {
            $catId = $data['bz_solr_category_delete_id'];
            $this->_getIndexer()->cleanIndex(null, $catId, 'category');
        } else if (!empty($data['bz_solr_category_update_id'])) {
            $catId = $data['bz_solr_category_update_id'];
            $catIds = array($catId);
            $this->_getIndexer()->rebuildIndex(null, $catIds, 'category');
        }
    }
    
    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        switch ($event->getEntity()) {
            case Mage_Catalog_Model_Category::ENTITY:
                $this->_registerCatalogCategoryEvent($event);
                break;
        }
    }
    
    /**
     * @param Mage_Index_Model_Event $event
     * @return this
     */
    protected function _registerCatalogCategoryEvent(Mage_Index_Model_Event $event)
    {
        switch ($event->getType()) {
            case Mage_Index_Model_Event::TYPE_SAVE:
                $category = $event->getDataObject();
                $event->addNewData('bz_solr_category_update_id', $category->getId());
                break;
            case Mage_Index_Model_Event::TYPE_DELETE:
                $category = $event->getDataObject();
                $event->addNewData('bz_solr_category_delete_id', $category->getId());
                break;
        }
        return $this;
    }
    
    public function reindexAll(){
        $resourceModel = $this->_getIndexer();
        try {
            $resourceModel->rebuildIndex(null,null,'category');
            //$resourceModel->commitChanges(); done in above already
        } catch (Exception $e) {
            $resourceModel->rollBackChanges();
            throw $e;
        }
    }
}
