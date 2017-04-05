<?php

class BZ_Solr_Model_Indexer_Indexer
{
    /**
     * Reindex of catalog search fulltext index using search engine
     *
     * @return BZ_Solr_Model_Indexer_Indexer
     */
    public function reindexAll()
    {
        $helper = Mage::helper('bz_solr');
        if ($helper->isThirdPartyEngineAvailable()) {
            /* Change index status to running */
            $indexProcess = Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_fulltext');
            if ($indexProcess) {
                $indexProcess->reindexAll();
            }
        }

        return $this;
    }
}
