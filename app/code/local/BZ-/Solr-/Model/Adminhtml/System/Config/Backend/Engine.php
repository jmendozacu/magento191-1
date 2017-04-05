<?php
/**
 * Class
 *
 * @author ben zhang <ben_zhanghf@hotmail.com>
 */
class BZ_Solr_Model_Adminhtml_System_Config_Backend_Engine extends Mage_Core_Model_Config_Data
{
    /**
     * After save call
     * Invalidate catalog search index if engine was changed
     *
     * @return BZ_Solr_Model_Adminhtml_System_Config_Backend_Engine
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        if ($this->isValueChanged()) {
            Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_fulltext')
                ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
            Mage::getSingleton('index/indexer')->getProcessByCode('bz_solr_indexer_cms')
                ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
            Mage::getSingleton('index/indexer')->getProcessByCode('bz_solr_indexer_category')
                ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }

        return $this;
    }
}
