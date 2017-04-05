<?php
/**
 * Class Suggestions
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Solr_Block_Suggestions extends Mage_Core_Block_Template
{
    /**
     * Retrieve search suggestions
     *
     * @return array
     */
    public function getSuggestions()
    {
        $helper = Mage::helper('bz_solr');

        $searchSuggestionsEnabled = (bool)$helper->getSolrConfigData('server_suggestion_enabled');
        if (!($helper->isThirdPartSearchEngine() && $helper->isActiveEngine()) || !$searchSuggestionsEnabled) {
            return array();
        }

        $suggestionsModel = Mage::getSingleton('bz_solr/suggestions');
        $suggestions = $suggestionsModel->getSearchSuggestions();

        foreach ($suggestions as $key => $suggestion) {
            $suggestions[$key]['link'] = $this->getUrl('*/*/') . '?q=' . urlencode($suggestion['word']);
        }

        return $suggestions;
    }

    /**
     * Retrieve search suggestions count results enabled
     *
     * @return boolean
     */
    public function isCountResultsEnabled()
    {
        return (bool)Mage::helper('bz_solr')->getSolrConfigData('server_suggestion_count_results_enabled');
    }
}