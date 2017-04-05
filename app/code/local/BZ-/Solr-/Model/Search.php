<?php
/**
 * Class Solr engine to simply search solr documents
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Solr_Model_Search extends BZ_Solr_Model_Adapter_Solr
{
    public function simpleSearch($keyword, $offset, $params, $limit = 5, $withSpellCheck = true)
    {
        try {
            $this->ping();
            $searchConditions = $this->prepareSearchConditions($keyword);
            if (!$searchConditions) {
                return array();
            }
            //set handler in solr search
            $store = Mage::app()->getStore();
            $params['fq'][] = 'store_id:'.$store->getId();
            $params['fq'][] = 'visibility:3 OR visibility:4';
            $locale = $store->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
            $languageSuffix = $this->_getLanguageSuffix($locale);
            if(!isset($params['wt'])) $params['wt'] = 'json';
            if (!isset($params['qt']))
                $params['qt'] = 'magento_auto' . $languageSuffix;
            if (!isset($params['fl']))
                $params['fl'] = 'score, *';
            if (!isset($params['sort']))
                $params['sort'] = 'score desc';
            if($withSpellCheck){
                $params['spellcheck'] = 'true';
                $params['spellcheck.dictionary'] = 'magento_spell'.$languageSuffix;
                $params['spellcheck.count'] = 10;
                $params['spellcheck.collate'] = 'true';
                $params['spellcheck.maxCollations'] = 10;
                $params['spellcheck.maxCollationTries'] = 10;
            }
            //grouping
            $params['group'] = 'true';
            $params['group.field'] = 'type';
            $params['group.limit'] = '5';
            $response = $this->_client->search(
                    $keyword, $offset, $limit, $params, Apache_Solr_Service::METHOD_POST
            );
            $raw = $response->getRawResponse();
            $json = preg_replace_callback('/"collation":/', function(&$cnt) {
                return '"collation-' . uniqid() . '":';
            }, $raw);
            //$data = json_decode($raw);
            $data = json_decode($json,true);
            if(isset($data['responseHeader']['status']) && $data['responseHeader']['status'] == 0){
                $docs = array();
                if(isset($data['grouped']['type']['groups']) && !empty($data['grouped']['type']['groups'])){
                    $docs = $data['grouped']['type']['groups'];
                }
                if($withSpellCheck && isset($data['spellcheck']['suggestions']) && !empty($data['spellcheck']['suggestions'])){
                    foreach($data['spellcheck']['suggestions'] as $key => $val){
                        if(strstr($key,'collation-')) $return_array_result['suggestions'][] = $val;
                    }
                }
                $return_array_result['groups'] = $docs;
                if(isset($return_array_result['suggestions']) && is_array($return_array_result['suggestions']))
                    $return_array_result['suggestions'] = array_unique($return_array_result['suggestions']);
                return $return_array_result;
            }else{
                $stats = $this->_prepateStatsQueryResponce($data);
                $return_array_result['groups'] = array();
                $return_array_result['stats'] = $stats;
                return $return_array_result;
            }
        }
        catch(Exception $e){
            Mage::logException($e);
        }
    }
}
