<?php
/**
 * Class Cms
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Solr_Model_Indexer_Cms extends Mage_Index_Model_Indexer_Abstract
{
    
    protected $_matchedEntities = array(
        Mage_Cms_Model_Page::CACHE_TAG => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE
        )
    );
    
    protected function _construct() {
        //$this->_init('bz_solr/indexer_cms');
        $helper = Mage::helper('bz_solr');
        if ($helper->isThirdPartyEngineAvailable()) $this->_isVisible = true;
        else $this->_isVisible = false;
    }
    
    public function getName()
    {
        return Mage::helper('bz_solr')->__('Solr CMS Search Index');
    }
    
    public function getDescription()
    {
        return Mage::helper('bz_solr')->__('Rebuild Solr CMS Search Index');
    }
    
    protected function _getIndexer()
    {
        return Mage::getResourceSingleton('bz_solr/indexer_cms');
    }
    
    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (!empty($data['bz_solr_cms_reindex_all'])) {
            $this->reindexAll();
        } else if (!empty($data['bz_solr_cms_delete_page_id'])) {
            $pageId = $data['bz_solr_cms_delete_page_id'];
            $this->_getIndexer()->cleanIndex(null, $pageId, 'cms');
        } else if (!empty($data['bz_solr_cms_update_page_id'])) {
            $pageId = $data['bz_solr_cms_update_page_id'];
            $pageIds = array($pageId);
            $this->_getIndexer()->rebuildIndex(null, $pageIds, 'cms');
        }
    }
    
    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        switch ($event->getEntity()) {
            case Mage_Cms_Model_Page::CACHE_TAG:
                $this->_registerCMSEvent($event);
                break;
        }
    }
    
    protected function _registerCMSEvent(Mage_Index_Model_Event $event)
    {
        switch ($event->getType()) {
            case Mage_Index_Model_Event::TYPE_SAVE:
                $page = $event->getDataObject();
                $event->addNewData('bz_solr_cms_update_page_id', $page->getId());
                break;
            case Mage_Index_Model_Event::TYPE_DELETE:
                $page = $event->getDataObject();
                $event->addNewData('bz_solr_cms_delete_page_id', $page->getId());
                break;
        }
        return $this;
    }
    
    public function reindexAll(){
        $resourceModel = $this->_getIndexer();
        try {
            $resourceModel->rebuildIndex(null,null,'cms');
            //$resourceModel->commitChanges(); it is done in the rebuildindex already
        } catch (Exception $e) {
            $resourceModel->rollBackChanges();
            throw $e;
        }
    }
}
