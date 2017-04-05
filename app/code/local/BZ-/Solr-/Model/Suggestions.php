<?php
class BZ_Solr_Model_Suggestions
{
    /**
     * Retrieve search suggestions
     * @return array
     */
    public function getSearchSuggestions()
    {
        return Mage::getSingleton('bz_solr/search_layer')
            ->getProductCollection()
            ->getSuggestionsData();
    }
}
