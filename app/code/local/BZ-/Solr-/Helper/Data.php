<?php
/**
 * Class Data
 *
 * @author ben zhang <ben_zhanghf@hotmail.com>
 */
class BZ_Solr_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_useEngineInLayeredNavigation    = null;

    protected $_languageCode                    = array();

    protected $_isThirdPartyEngineAvailable     = null;

    protected $_taxInfluence                    = null;

    protected $_isEngineAvailableForNavigation  = null;

    protected $_textFieldTypes = array(
        'text',
        'varchar'
    );

    /**
     * Retrive text field types
     * @return array
     */
    public function getTextFieldTypes()
    {
        return $this->_textFieldTypes;
    }

    /**
     * Retrive supported by Solr languages including locale codes (language codes) that are specified in configuration
     * Array(
     *      'language_code1' => 'locale_code',
     *      'language_code2' => Array('locale_code1', 'locale_code2')
     * )
     * @return array
     */
    public function getSolrSupportedLanguages()
    {
        $default = array(
            /**
             * SnowBall filter based
             */
            //Danish
            'da' => 'da_DK',
            //Dutch
            'nl' => 'nl_NL',
            //English
            'en' => array('en_AU', 'en_CA', 'en_NZ', 'en_GB', 'en_US'),
            //Finnish
            'fi' => 'fi_FI',
            //French
            'fr' => array('fr_CA', 'fr_FR'),
            //German
            'de' => array('de_DE','de_CH','de_AT'),
            //Italian
            'it' => array('it_IT','it_CH'),
            //Norwegian
            'nb' => array('nb_NO', 'nn_NO'),
            //Portuguese
            'pt' => array('pt_BR', 'pt_PT'),
            //Romanian
            'ro' => 'ro_RO',
            //Russian
            'ru' => 'ru_RU',
            //Spanish
            'es' => array('es_AR', 'es_CL', 'es_CO', 'es_CR', 'es_ES', 'es_MX', 'es_PA', 'es_PE', 'es_VE'),
            //Swedish
            'sv' => 'sv_SE',
            //Turkish
            'tr' => 'tr_TR',
            /**
             * Lucene class based
             */
            //Czech
            'cs' => 'cs_CZ',
            //Greek
            'el' => 'el_GR',
            //Thai
            'th' => 'th_TH',
            //Chinese
            'zh' => array('zh_CN', 'zh_HK', 'zh_TW'),
            //Japanese
            'ja' => 'ja_JP',
            //Korean
            'ko' => 'ko_KR'
        );

        /**
         * Merging languages that specified manualy
         */
        $node = Mage::getConfig()->getNode('global/bz_solr/supported_languages/solr');
        if ($node && $node->children()) {
            foreach ($node->children() as $_node) {
                $localeCode = $_node->getName();
                $langCode   = $_node . '';
                if (isset($default[$langCode])) {
                    if (is_array($default[$langCode])) {
                        if (!in_array($localeCode, $default[$langCode])) {
                            $default[$langCode][] = $localeCode;
                        }
                    } elseif ($default[$langCode] != $localeCode) {
                        $default[$langCode] = array($default[$langCode], $localeCode);
                    }
                } else {
                    $default[$langCode] = $localeCode;
                }
            }
        }

        return $default;
    }

    /**
     * Retrieve information from Solr search engine configuration
     *
     * @param string $field
     * @param int $storeId
     * @return string|int
     */
    public function getSolrConfigData($field, $storeId = null)
    {
        return $this->getSearchConfigData('solr_' . $field, $storeId);
    }

    /**
     * Retrieve information from search engine configuration
     *
     * @param string $field
     * @param int $storeId
     * @return string|int
     */
    public function getSearchConfigData($field, $storeId = null)
    {
        $path = 'catalog/solr_search/' . $field;
        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Return true if third party search engine is used
     *
     * @return bool
     */
    public function isThirdPartSearchEngine()
    {
        $engine = $this->getSearchConfigData('engine');
        if ($engine == 'bz_solr/engine') {
            return true;
        }

        return false;
    }

    /**
     * Retrieve language code by specified locale code if this locale is supported
     *
     * @param  string $localeCode
     * @return string|false
     */
    public function getLanguageCodeByLocaleCode($localeCode)
    {
        $localeCode = (string)$localeCode;
        if (!$localeCode) {
            return false;
        }

        if (!isset($this->_languageCode[$localeCode])) {
            $languages = $this->getSolrSupportedLanguages();

            $this->_languageCode[$localeCode] = false;
            foreach ($languages as $code => $locales) {
                if (is_array($locales)) {
                    if (in_array($localeCode, $locales)) {
                        $this->_languageCode[$localeCode] = $code;
                    }
                } elseif ($localeCode == $locales) {
                    $this->_languageCode[$localeCode] = $code;
                }
            }
        }

        return $this->_languageCode[$localeCode];
    }

    /**
     * Prepare language suffix for text fields.
     * For not supported languages prefix _def will be returned.
     *
     * @param  string $localeCode
     * @return string
     */
    public function getLanguageSuffix($localeCode)
    {
        $languageCode = $this->getLanguageCodeByLocaleCode($localeCode);
        if (!$languageCode) {
            $languageCode = 'def';
        }
        $languageSuffix = '_' . $languageCode;

        return $languageSuffix;
    }
    
    public function isActiveEngine()
    {
        $engine = $this->getSearchConfigData('engine');
        if ($engine && Mage::getConfig()->getResourceModelClassName($engine)) {
            $model = Mage::getResourceSingleton($engine);
            if ($model && $model->test() && $model->allowAdvancedIndex()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if third party engine is selected and active
     *
     * @return bool
     */
    public function isThirdPartyEngineAvailable()
    {
        if ($this->_isThirdPartyEngineAvailable === null) {
            $this->_isThirdPartyEngineAvailable = ($this->isThirdPartSearchEngine() && $this->isActiveEngine());
        }
        return $this->_isThirdPartyEngineAvailable;
    }

    /**
     * Check if taxes have influence on price
     *
     * @return bool
     */
    public function getTaxInfluence()
    {
        /*if (is_null($this->_taxInfluence)) {
            $this->_taxInfluence = (bool) Mage::helper('tax')->getPriceTaxSql('price', 'tax');
        }
        return $this->_taxInfluence;*/
        return false;
    }

    /**
     * Check if search engine can be used for catalog navigation
     *
     * @param   bool $isCatalog - define if checking availability for catalog navigation or search result navigation
     * @return  bool
     */
    public function getIsEngineAvailableForNavigation($isCatalog = true)
    {
        if (is_null($this->_isEngineAvailableForNavigation)) {
            $this->_isEngineAvailableForNavigation = false;
            if ($this->isActiveEngine()) {
                if ($isCatalog) {
                    if ($this->getSearchConfigData('solr_server_use_in_catalog_navigation')
                        && !$this->getTaxInfluence()
                    ) {
                        $this->_isEngineAvailableForNavigation = true;
                    }
                } else {
                    $this->_isEngineAvailableForNavigation = true;
                }
            }
        }

        return $this->_isEngineAvailableForNavigation;
    }
}
