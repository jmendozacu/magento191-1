<?php

/**
 * Class Netstarter_Retaildirections_Model_Translation_Product
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 *
 * Actual translates API data into Magento data in a 2step importing process.
 * This class contains most of the business logic for product import.
 *
 */
class Netstarter_Retaildirections_Model_Translation_Product extends Netstarter_Retaildirections_Model_Translation_Abstract
{
    /**
     * Current default attribute set ID. Set in the backend.
     *
     * @var null|string
     */
    protected $_defaultAttributeSetId           = null;

    /**
     * For each Magento website, this array contains its website default store.
     *
     * @var array
     */
    protected $_defaultStoreByWebsite           = array();

    /**
     * Array of current website IDs.
     *
     * @var null|array
     */
    protected $_websiteCodesList                = null;

    /**
     * Array of current store IDs.
     *
     * @var null|array
     */
    protected $_storesList                      = null;

    /**
     * For each website, this array contains all the categories
     * that are part of that website.
     *
     * @var array
     */
    protected $_websiteCategoryGroups           = array();

    /**
     * The product model that will be used to create simple products.
     *
     * @var null|Mage_Catalog_Model_Product
     */
    protected $_productModelSimple              = null;

    /**
     * The product model that will be used to create configurable products.
     *
     * @var null|Mage_Catalog_Model_Product
     */
    protected $_productModelConfigurable        = null;

    /**
     * EAV attribute model
     *
     * @var null|Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected $_attributeEavModel               = null;

    /**
     * Attribute setup model (in case of necessary creation of options values)
     *
     * @var null|Mage_Eav_Model_Entity_Setup
     */
    protected $_attributeSetupModel             = null;

    /**
     * EAV Entity attribute model
     *
     * @var null|Mage_Eav_Model_Resource_Entity_Attribute
     */
    protected $_eavResourceModel                = null;

    /**
     * Flat API returned product model
     *
     * @var null|Netstarter_Retaildirections_Model_Product
     */
    protected $_flatProductModel                = null;

    /**
     * Products to be translated into Magento collection
     *
     * @var null|Netstarter_Retaildirections_Model_Resource_Product_Collection
     */
    protected $_flatProductCollection           = null;

    /**
     * All colors of a product that needs to be translated into Magento
     *
     * @var null|Netstarter_Retaildirections_Model_Resource_Product_Collection
     */
    protected $_flatProductItemCodesToUpdate    = null;

    /**
     * Array of attribute Ids based on the attribute code
     *
     * @var array
     */
    protected $_attributeIds                    = array();

    /**
     * Array of attribute models, per attribute code
     *
     * @var array
     */
    protected $_attributeModels                 = array();

    /**
     * Array of last processed data, accumulated per simple/configurable interaction
     *
     * @var array
     */
    protected $_lastProcessedSimpleData         = array();

    /**
     * XML path to Magento configuration: is this the default website where product data is copied from?
     */
    const CONFIG_PATH_ATTRIBUTE_SET_ID          = 'netstarter_retaildirections/product/attribute_set_id';

    /**
     * XML path to Magento configuration: is this the default website where product data is copied from?
     */
    const CONFIG_PATH_TAX_ID          = 'netstarter_retaildirections/product/tax_id';
    
    /**
     * XML path to Magento configuration: default category ID
     */
    const CONFIG_PATH_DEFAULT_CATEGORY_ID       = 'netstarter_retaildirections/product/default_category_id';

    /**
     * XML path to Magento configuration: default category ID for "on sale" products
     */
    const CONFIG_PATH_ON_SALE_CATEGORY_ID       = 'netstarter_retaildirections/product/on_sale_category_id';
    
    /**
     * XML path to Magento configuration: should we treat all products as core or non-core, overriding the API value?
     */
    const CONFIG_PATH_CORE_NONCORE_OVERWRITE    = 'netstarter_retaildirections/product/corenoncore_overwrite';
    
    /**
     * XML path to Magento configuration: default should be core or noncore?
     */
    const CONFIG_PATH_CORE_NONCORE_DEFAULT      = 'netstarter_retaildirections/product/corenoncore_default';
    
    /**
     * XML path to Magento configuration: SKU blacklist
     */
    const CONFIG_PATH_CORE_SKU_BLACKLIST        = 'netstarter_retaildirections/product/sku_blacklist';

    /**
     * General settings that change script behaviour
     * This states on which level of the RD structure we will create the Magento configurable product.
     *
     * 'item_colour_ref' => colour level.
     */
    const CONFIGURABLE_GROUP_BY                 = 'item_colour_ref';

    /**
     * General settings that change script behaviour
     */
    const ALLOW_PRICE_CHANGES_IN_CONFIGURABLE   = false;
    
    /**
     * General settings that change script behaviour
     */
    const ALLOW_CATEGORY_MOVEMENT               = false;

    /**
     * General settings that change script behaviour while creating URLs for a product.
     */
    const MAX_URL_ITERATIONS                    = 100;

    /**
     * Used in "website_attribute"
     */
    const SCOPE_SEPARATOR                       = "_";

    /**
     * Constant for product is not on sale attribute
     */
    const PRODUCT_IS_NOT_ON_SALE                = "0";

    /**
     * Constant for product is on sale attribute
     */
    const PRODUCT_IS_ON_SALE                    = "1";

    /*
     * Flat data value for the season_code attribute that means a product is core
     * "ongoing"
     */
    const PRODUCT_IS_CORE_VALUE                 = 'ONG';

    /**
     * Constant for the current price on the API
     */
    const FLAT_PRICE_FIELD                      = 'current_price';

    /**
     * Constant for the RRP price on the API
     */
    const FLAT_RRP_FIELD                        = 'rrp';

    /**
     * Constant for the RRP price on Magento
     */
    const MAGENTO_PRICE_FIELD                   = 'price';

    /**
     * Constant for the current price on Magento
     */
    const MAGENTO_SPECIAL_PRICE_FIELD           = 'special_price';

    /**
     * Appended to multiselect attribute names so it is possible to retrieve its label
     */
    const LABEL_DATA_APPEND                     = '_label_data';

    /**
     * Run mode we are currently at, are we creating a new product in Magento or updating an existing one?
     */
    const MODE_UPDATE                           = 'update';

    /**
     * Run mode we are currently at, are we creating a new product in Magento or updating an existing one?
     */
    const MODE_CREATE                           = 'create';

    /**
     * Accumulated processed data from simple products to be used in the configurable.
     */
    const TYPE_LASTPROCESSED_ONSALE_DATA                 = 'on_sale_data';

    /**
     * Accumulated processed data from simple products to be used in the configurable.
     */
    const TYPE_LASTPROCESSED_STOCK                       = 'stock';

    /**
     * Accumulated processed data from simple products to be used in the configurable.
     */
    const TYPE_LASTPROCESSED_NAMES                       = 'names';

    /**
     * Accumulated processed data from simple products to be used in the configurable.
     * This is a magic keyword that refers to all others. Used when cleaning the array.
     */
    const TYPE_LASTPROCESSED_ALL                         = 'all';

    /**
     * Accumulated processed data from simple products to be used in the configurable.
     */
    const TYPE_LASTPROCESSED_CATEGORIES                  = 'categories';

    /**
     * Accumulated processed data from simple products to be used in the configurable.
     */
    const TYPE_LASTPROCESSED_CONFIGURABLE_SIMPLE_DATA    = 'simple_data';

    /**
     * Accumulated processed data from simple products to be used in the configurable.
     */
    const TYPE_LASTPROCESSED_CONFIGURABLE_ATTRIBUTE_DATA = 'attribute_data';

    /**
     * Accumulated processed data from simple products to be used in the configurable.
     */
    const TYPE_LASTPROCESSED_CONFIGURABLE_ADDED_ID       = 'added_id';

    /**
     * Product attributes translation from API data to Magento attribute
     *
     * @var array
     */
    protected $_productAttributes               = array(
        'description' => 'name',
        'short_description' => 'short_description',
        'extended_description' => 'description',
        'sell_code_code' => 'sku',
        'item_code' => 'item_code',
        'size_code' => 'size',
        'base_colour_description' => 'color',
        'base_colour_name' => 'simple_color',
        'category_description' => 'rd_category',
        self::FLAT_PRICE_FIELD => self::MAGENTO_PRICE_FIELD
    );

    /**
     * Attributes that are part of an configurable product
     * Values here need also to be on the $_multiselectAttributes array
     *
     * @var array
     */
    protected $_configurableAttributes          = array(
//        'color' => true, // price level, as it was on the first integration implementation
        'size' => false,
    );

    /**
     * Attributes that are part of an unique combination for simple product name
     * Values here need also to be on the $_multiselectAttributes array
     *
     * @var array
     */
    protected $_simpleProductNameUniqueness     = array(
        'color' => false,
        'size' => false,
    );

    /**
     * Attributes that have the type "multiselect"
     *
     * @var array
     */
    protected $_multiselectAttributes          = array(
        'color' => false,
        'size' => false,
        'simple_color' => false
    );

    /**
     * These are the Magento attributes allowed to be updated in a subsequent translation after creation.
     * @TODO fix the need to append data here
     *
     * @var array
     */
    protected $_allowUpdateAttributes          = array(
        'color',
        'size',
        'simple_color',
        'highest_price',
        'color_label_data',
        'simple_color_label_data',
        'size_label_data',
//        'description',
        'stock_data',
        'category_ids',
        'on_sale',
        'previous_categories_ids',
        'is_noncore',
        self::MAGENTO_PRICE_FIELD,
        self::MAGENTO_SPECIAL_PRICE_FIELD,
    );

    /**
     * Attributes in Magento that have different values per website
     *
     * @var array
     */
    protected $_productAttributesWebsite        = array(
        self::FLAT_RRP_FIELD => self::MAGENTO_PRICE_FIELD,
        self::FLAT_PRICE_FIELD => self::MAGENTO_SPECIAL_PRICE_FIELD,
        'highest_price' => 'highest_price',
    );
    
    /**
     * Variables to cater for logging and locking mechanisms
     * Please see Netstarter_Shelltools
     */
    protected $_countForReport                  = array(
        'success'   => 0,
        'error'     => 0,
    );
    protected $_jobId           = 'PRODUCT_TRANSLATION';
    protected $_logReportMode   = self::LOG_REPORT_MODE_EMAIL;
    protected $_lock            = true;
    
    /**
     * SKUs that will be ignored
     * 
     * @var array
     */
    protected $_blacklist       = null;
    
    /**
     * On sale categories (needs to be more than on
     * due to the chosen layered navigation scrtucture)
     * 
     * @var array
     */
    protected $_categoriesOnSaleList = array();
    
    /**
     * Retrieve category array of on-sale
     * categories
     * 
     * @return array
     */
    protected function _getOnSaleCategories($websiteCode)
    {
        if (!array_key_exists($websiteCode, $this->_categoriesOnSaleList))
        {
            // comma separated list is expected
            $_categoriesOnSaleList = (string) Mage::getConfig()->getNode
                (
                    self::CONFIG_PATH_ON_SALE_CATEGORY_ID,
                    'website',
                    $websiteCode
                );
            
            $this->_categoriesOnSaleList[$websiteCode] = explode(",",$_categoriesOnSaleList);
        }
        
        return $this->_categoriesOnSaleList[$websiteCode];
    }

    /**
     * Structure used to store temporary data, used inside a individual
     * configurable product relation sequence.
     *
     * This method append data into an array structure.
     *
     * @param string $type Relative to TYPE_ constants in this class
     * @param mixed $data
     * @return $this
     */
    protected function _appendLastProcessedSimpleData($type, $data)
    {
        if (!is_array($this->_lastProcessedSimpleData))
        {
            $this->_lastProcessedSimpleData = array();
        }

        if (!array_key_exists($type, $this->_lastProcessedSimpleData))
        {
            $this->_lastProcessedSimpleData[$type] = array();
        }

        if (!is_array($this->_lastProcessedSimpleData[$type]))
        {
            $this->_lastProcessedSimpleData[$type] = array();
        }

        if (!is_array($data))
        {
            $data = array($data);
        }

        // insert data to be used later
        array_push($this->_lastProcessedSimpleData[$type], implode('', $data));

        return $this;
    }

    /**
     * Structure used to store temporary data, used inside a individual
     * configurable product relation sequence
     *
     * This method sets a specific key into the array
     *
     * @param string $type Relative to TYPE_ constants in this class
     * @param mixed $data
     * @return $this
     */
    protected function _setLastProcessedSimpleData($type, $data)
    {
        if (!is_array($this->_lastProcessedSimpleData))
        {
            $this->_lastProcessedSimpleData = array();
        }

        $this->_lastProcessedSimpleData[$type] = $data;

        return $this;
    }

    /**
     * Get last data inserted, based on the type.
     *
     * Structure used to store temporary data.
     *
     * @param $type Relative to TYPE_ constants in this class
     * @param bool $setArray
     * @return array|bool|string
     */
    protected function _getLastProcessedSimpleData($type, $setArray = true)
    {
        $return = $setArray ? array() : false;

        if (!is_array($this->_lastProcessedSimpleData))
        {
            return $return;
        }

        if (!array_key_exists($type, $this->_lastProcessedSimpleData))
        {
            return $return;
        }

        if (!isset($this->_lastProcessedSimpleData[$type]))
        {
            return $return;
        }

        switch ($type)
        {
            // specific preprocessing type-dependant
            case self::TYPE_LASTPROCESSED_NAMES:
                array_walk($this->_lastProcessedSimpleData[$type], array($this, '_callBackFixArrayValues'));
                return implode(' ', $this->_lastProcessedSimpleData[$type]);
                break;
            // or default just return
            default:
                return $this->_lastProcessedSimpleData[$type];
                break;
        }

        return false;
    }

    /**
     * Get COUNT of last data inserted, based on the type.
     * Structure used to store temporary data.
     *
     * @param string $type Relative to TYPE_ constants in this class
     * @return bool|int
     */
    protected function _getLastProcessedSimpleDataAmount($type = self::TYPE_LASTPROCESSED_NAMES)
    {
        if (!is_array($this->_lastProcessedSimpleData))
        {
            return false;
        }

        if (!array_key_exists($type, $this->_lastProcessedSimpleData))
        {
            return false;
        }

        if (!is_array($this->_lastProcessedSimpleData[$type]))
        {
            return false;
        }

        return count($this->_lastProcessedSimpleData[$type]);
    }


    /**
     * Used to clean array from spaces for self::TYPE_LASTPROCESSED_NAMES
     * and to generate a more readable product name. This is used in case
     * the configurable name have all the colors.
     *
     * @param string $value
     */
    protected function _callBackFixArrayValues(&$value)
    {
        $value = str_replace(' ', '', $value);
    }

    /**
     * Clear the processed data array. This is called every time we finished
     * processing a configurable, and start processing another one.
     *
     * The TYPE_LASTPROCESSED_ALL keyword makes us clean the whole array.
     *
     * @param string $type
     * @return $this
     */
    protected function _clearLastProcessedSimpleData($type = self::TYPE_LASTPROCESSED_ALL)
    {
        if (!is_array($this->_lastProcessedSimpleData))
        {
            return $this;
        }

        // Use Short-circuit evaluation - as the self::TYPE_LASTPROCESSED_ALL index won't exist.
        if ($type != self::TYPE_LASTPROCESSED_ALL && !is_array($this->_lastProcessedSimpleData[$type]))
        {
            return $this;
        }

        switch ($type)
        {
            case self::TYPE_LASTPROCESSED_ALL:
                $this->_lastProcessedSimpleData = array();
                break;
            default:
                $this->_lastProcessedSimpleData[$type] = array();
                break;
        }

        return $this;
    }

    /**
     * Returns an attribute ID based on its code.
     *
     * @param $attribute
     * @return mixed
     */
    protected function _getAttributeId($attribute)
    {
        if (!array_key_exists($attribute, $this->_attributeIds))
        {
            $this->_attributeIds[$attribute] = $this->_getEavResourceModel()->getIdByCode('catalog_product', $attribute);
        }

        return $this->_attributeIds[$attribute];
    }

    /**
     * Returns an attribute model, based on its code.
     *
     * @param $attribute
     * @return mixed
     */
    protected function _getAttributeModel($attribute)
    {
        if (!array_key_exists($attribute, $this->_attributeModels))
        {
            $this->_attributeModels[$attribute] = clone $this->_getAttributeEavModel();
            $this->_attributeModels[$attribute]->load($this->_getAttributeId($attribute));
        }

        return $this->_attributeModels[$attribute];
    }

    /**
     * Returns the Setup model for attributes. This is used in case
     * a new value for colors come from the API, e.g. new color "brown"
     * for the "color" attribute. In this case we create this new value for
     * the attribute in Magento.
     *
     * @return Mage_Eav_Model_Entity_Setup|null
     */
    protected function _getAttributeSetupModel()
    {
        if ($this->_attributeSetupModel == null)
        {
            $this->_attributeSetupModel = new Mage_Eav_Model_Entity_Setup('core_setup');
        }

        return $this->_attributeSetupModel;
    }

    /**
     * Unsets a currently being used attribute model.
     * In case the model is changed, this reinforces it to be loaded again from scratch.
     *
     * @param $attribute
     * @return $this
     */
    protected function _unsetAttributeModel($attribute)
    {
        if (array_key_exists($attribute, $this->_attributeModels))
        {
            unset ($this->_attributeModels[$attribute]);
        }

        return $this;
    }

    /**
     * Creates a new option value into a select/multiselect attribute.
     *
     * @param $attribute
     * @param $value
     */
    protected function _createAttributeValue($attribute, $value)
    {
        $storesArray = array($value);

        foreach ($this->_getDefaultStoresArray() as $id)
        {
            $storesArray[$id] = $value;
        }

        $this->_getAttributeSetupModel()->addAttributeOption(
            array(
                'attribute_id' => $this->_getAttributeId($attribute),
                'value' => array
                (
                    $storesArray,
                ),
            )
        );
    }

    /**
     * For an specific option from an attribute, gets the ID of it based on the LABEL.
     * e.g.: for the attribute "color" we have the value "brown" to save into the database,
     * however what really goes into the database is 50, which is the "brown" label ID.
     *
     * @param $attribute
     * @param $value
     * @return int
     */
    protected function _getAttributeValueId($attribute, $value)
    {
        $attributeOptions   = $this->_getAttributeModel($attribute)->getSource()->getAllOptions();
        $valueIndex         = 0;

        foreach ($attributeOptions as $opts_arr)
        {
            // compare each label to get its ID
            if (strtoupper($opts_arr['label']) == strtoupper($value))
            {
                $valueIndex =  $opts_arr['value'];
            }
        }

        return $valueIndex;
    }

    /**
     * Tries to retrieve the LABEL ID from an attribute.
     * If label doesn't exists, creates it and returns the new ID.
     *
     * @param $attribute
     * @param $value
     * @param bool $create
     * @return mixed
     */
    protected function _getAttributeValueIdAndCreate($attribute, $value, $create = true)
    {
        $valueIndex = $this->_getAttributeValueId($attribute, $value);

        if (0 == $valueIndex && $create)
        {
            $this->_createAttributeValue($attribute, $value);
        }

        $valueIndex = $this->_unsetAttributeModel($attribute)->_getAttributeValueId($attribute, $value);
        return $valueIndex;
    }

    /**
     * Return the product model for a simple product.
     *
     * It is necessary to use different product models for each type of
     * product you are going to create. This is because internally the product model
     * has a reference to the product type model that is kept even if you use clearInstance
     * to use the model again. So, if you create a simple product and then tries to create
     * a configurable after you won't be successful.
     *
     * @param $websiteCode
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProductModelSimple($websiteCode)
    {
        if (!is_array($this->_productModelSimple))
        {
            $this->_productModelSimple = array();
        }

        if (! array_key_exists($websiteCode, $this->_productModelSimple))
        {
            $this->_productModelSimple[$websiteCode] = Mage::getModel('catalog/product');
        }

        return $this->_productModelSimple[$websiteCode];
    }

    /**
     * Return the product model for a configurable product.
     *
     * It is necessary to use different product models for each type of
     * product you are going to create. This is because internally the product model
     * has a reference to the product type model that is kept even if you use clearInstance
     * to use the model again. So, if you create a simple product and then tries to create
     * a configurable after you won't be successful.
     *
     * @param $websiteCode
     * @return false|Mage_Catalog_Model_Product|null
     */
    protected function _getProductModelConfigurable($websiteCode)
    {
        if (!is_array($this->_productModelConfigurable))
        {
            $this->_productModelConfigurable = array();
        }
        
        if (! array_key_exists($websiteCode, $this->_productModelConfigurable))
        {
            $this->_productModelConfigurable[$websiteCode] = Mage::getModel('catalog/product');
        }

        return $this->_productModelConfigurable[$websiteCode];
    }

    /**
     * @return false|Mage_Catalog_Model_Resource_Eav_Attribute|null
     */
    protected function _getAttributeEavModel()
    {
        if ($this->_attributeEavModel == null)
        {
            $this->_attributeEavModel = Mage::getModel('catalog/resource_eav_attribute');
        }

        return $this->_attributeEavModel;
    }

    /**
     * @return null|Mage_Eav_Model_Resource_Entity_Attribute
     */
    protected function _getEavResourceModel()
    {
        if ($this->_eavResourceModel == null)
        {
            $this->_eavResourceModel = Mage::getResourceModel('eav/entity_attribute');
        }

        return $this->_eavResourceModel;
    }

    /**
     * Where the API data is kept.
     *
     * @return false|Netstarter_Retaildirections_Model_Product|null
     */
    protected function _getFlatProductModel()
    {
        if ($this->_flatProductModel == null)
        {
            $this->_flatProductModel = Mage::getModel('netstarter_retaildirections/product');
        }

        return $this->_flatProductModel;
    }

    /**
     * Get the collection of products we will need to translate.
     *
     * In case of grouping products by colours, all sizes of that product will be
     * translated.
     *
     * @return null|Netstarter_Retaildirections_Model_Resource_Product_Collection
     */
    protected function _getFlatProductItemCodesToTranslate()
    {
        if ($this->_flatProductItemCodesToUpdate == null)
        {
            $this->_flatProductItemCodesToUpdate = $this->_getFlatProductCollection()
                ->getBaseItemCodesToTranslate(self::CONFIGURABLE_GROUP_BY);
        }

        return $this->_flatProductItemCodesToUpdate;
    }

    /**
     * @return null|Netstarter_Retaildirections_Model_Resource_Product_Collection
     */
    protected function _getFlatProductCollection()
    {
        if ($this->_flatProductCollection == null)
        {
            $this->_flatProductCollection = $this->_getFlatProductModel()->getCollection();
        }

        return $this->_flatProductCollection;
    }

    /**
     * Get the default attribute set ID where the product will be created on.
     *
     * @return mixed|null
     */
    protected function _getDefaultAttributeSetId()
    {
        if ($this->_defaultAttributeSetId == null)
        {
            $this->_defaultAttributeSetId = Mage::getStoreConfig(self::CONFIG_PATH_ATTRIBUTE_SET_ID);
        }

        return $this->_defaultAttributeSetId;
    }

    /**
     * Get the default website array where the product will be created on.
     *
     * @return array|null
     */
    protected function _getDefaultWebsiteArray()
    {
        if ($this->_websiteCodesList == null)
        {
            $this->_websiteCodesList = array();
            foreach (Mage::app()->getWebsites() as $website)
            {
                array_push($this->_websiteCodesList, $website->getId());
            }
        }

        return $this->_websiteCodesList;
    }

    /**
     * An array containing each default store ID for each website.
     *
     * @return array|null
     */
    protected function _getDefaultStoresArray()
    {
        if ($this->_storesList == null)
        {
            $this->_storesList = array();
            foreach (Mage::app()->getWebsites() as $website)
            {
                foreach ($website->getStores() as $store)
                {
                    array_push($this->_storesList, $store->getId());
                }
            }
        }

        return $this->_storesList;
    }

    /**
     * Get the default store ID or model for a specific website.
     *
     * @param $websiteCode
     * @param bool $returnModel
     * @return int|Mage_Core_Model_Store
     */
    protected function _getDefaultStoresByWebsite($websiteCode, $returnModel = false)
    {
        if ($returnModel)
        {
            $store = Mage::getModel('core/website')->load($websiteCode)->getDefaultStore();
            return $store;
        }

        if (!is_array($this->_defaultStoreByWebsite))
        {
            $this->_defaultStoreByWebsite = array();
        }

        if (!array_key_exists($websiteCode, $this->_defaultStoreByWebsite))
        {
            $store = Mage::getModel('core/website')->load($websiteCode)->getDefaultStore();
            $this->_defaultStoreByWebsite[$websiteCode] = $store->getId();
        }

        return $this->_defaultStoreByWebsite[$websiteCode];
    }

    /**
     * Process if product is core or non-core as per criteria below:
     *   -- season_code
     *   -- 13S / seasonal (non-core)
     *   -- ONG / core
     *   -- DIS / discontinued
     *
     * The attribute is the same for any scope.
     * There is a backend option to overwrite this behaviour.
     *
     * @param $itemColourSizeFlat
     * @return int
     */
    protected function _getFlatIsNoncore($itemColourSizeFlat)
    {
        $shouldOverwrite    = Mage::getStoreConfigFlag(self::CONFIG_PATH_CORE_NONCORE_OVERWRITE);
        
        if ($shouldOverwrite)
        {
            $apiValue = Mage::getStoreConfig(self::CONFIG_PATH_CORE_NONCORE_DEFAULT);
        }
        else
        {
            $apiValue = intval(!($itemColourSizeFlat->getData('season_code') == self::PRODUCT_IS_CORE_VALUE));
        }
        
        return $apiValue;
    }

    /**
     * Prepares basic standard data that will be used on all products.
     *
     * @param $itemColourSizeFlat
     * @param string $mode
     * @return array
     */
    protected function _prepareData ($itemColourSizeFlat, $mode = self::MODE_CREATE)
    {
        $preparedAttributes = array();
        foreach ($itemColourSizeFlat->getData() as $k => $v)
        {
            if (array_key_exists($k, $this->_productAttributes))
            {
                $preparedAttributes[$this->_productAttributes[$k]] = $v;
            }
        }

        $preparedAttributes['status']           = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
        $preparedAttributes['visibility']       = Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;
        $preparedAttributes['tax_class_id']     = Mage::getStoreConfig(self::CONFIG_PATH_TAX_ID);
        $preparedAttributes['on_sale']          = self::PRODUCT_IS_NOT_ON_SALE;
        $preparedAttributes['attribute_set_id'] = $this->_getDefaultAttributeSetId();
        $preparedAttributes['website_ids']      = $this->_getDefaultWebsiteArray();
        $preparedAttributes['is_noncore']       = $this->_getFlatIsNoncore($itemColourSizeFlat);
        
        $preparedAttributes['name']             = substr($preparedAttributes['name'], 0, 100);

        foreach ($this->_multiselectAttributes as $attribute => $originalAttribute)
        {
            if (!array_key_exists($attribute, $preparedAttributes))
            {
                continue;
            }

            // save temporarily the label for the attribute and changes it for the product ID
            // the label will still be used later on in the process
            $preparedAttributes[$attribute.self::LABEL_DATA_APPEND] = $preparedAttributes[$attribute];
            $preparedAttributes[$attribute] = $this->_getAttributeValueIdAndCreate(
                $attribute,
                $preparedAttributes[$attribute]
            );
        }

        $categoriesArray = array();
        foreach($this->_getWebsitesData() as $websiteCode => $isDefault)
        {
            array_push($categoriesArray,
                (string) Mage::getConfig()->getNode(self::CONFIG_PATH_DEFAULT_CATEGORY_ID, 'website', $websiteCode));
        }

        $preparedAttributes['category_ids']     = $categoriesArray;

        return $preparedAttributes;
    }

    /**
     * Calculates final price attributes.
     *
     * @param $itemColourSizeFlat
     * @param $preparedAttributes
     * @param null $websiteCode
     * @return mixed
     */
    protected function _prepareDataSimpleFinalPrice($itemColourSizeFlat, $preparedAttributes, $websiteCode = null)
    {
	if ($websiteCode == "bntau"){ $websiteCode = null; }

        if ($websiteCode == null)
        {
            $flatPrice  = $itemColourSizeFlat->getData(self::FLAT_PRICE_FIELD);
            $rrpPrice   = $itemColourSizeFlat->getData(self::FLAT_RRP_FIELD);

        }
        else
        {
            $flatPrice  = $itemColourSizeFlat->getData($websiteCode . self::SCOPE_SEPARATOR .self::FLAT_PRICE_FIELD);
            $rrpPrice   = $itemColourSizeFlat->getData($websiteCode . self::SCOPE_SEPARATOR .self::FLAT_RRP_FIELD);
        }

        if ($flatPrice > $rrpPrice)
        {
            $flatPrice = $rrpPrice;
        }

        $preparedAttributes[self::MAGENTO_PRICE_FIELD] = $flatPrice;
        $preparedAttributes[self::MAGENTO_SPECIAL_PRICE_FIELD] = '';
        $preparedAttributes['on_sale'] = self::PRODUCT_IS_NOT_ON_SALE;

        if ($flatPrice != $rrpPrice)
        {
            $preparedAttributes[self::MAGENTO_PRICE_FIELD] = $rrpPrice;
            $preparedAttributes[self::MAGENTO_SPECIAL_PRICE_FIELD] = $flatPrice;
            $preparedAttributes['on_sale'] = self::PRODUCT_IS_ON_SALE;
        }

        return $preparedAttributes;
    }

    /**
     * Prepare data for simple products that are different among different stores/websites.
     *
     * @param $itemColourSizeFlat
     * @param $websiteCode Current scope being prepared
     * @return array
     */
    protected function _prepareDataSimpleScoped ($itemColourSizeFlat, $websiteCode)
    {
        foreach ($this->_productAttributesWebsite as $k => $v)
        {
            $scopedValue = $websiteCode . self::SCOPE_SEPARATOR . $k;
            if($itemColourSizeFlat->hasData($scopedValue))
            {
                $preparedAttributes[$v] = $itemColourSizeFlat->getData($scopedValue);
            }
        }

        $preparedAttributes = $this->_prepareDataSimpleFinalPrice($itemColourSizeFlat, $preparedAttributes, $websiteCode);

        return $preparedAttributes;
    }

    /**
     * Prepare data for configurable products that are different among different stores/websites.
     *
     * @param $itemColourSizeFlat
     * @param $websiteCode Current scope being prepared
     * @return array
     */
    protected function _prepareDataConfigurableScoped ($itemColourSizeFlat, $websiteCode)
    {
        foreach ($this->_productAttributesWebsite as $k => $v)
        {
            $scopedValue = $websiteCode . self::SCOPE_SEPARATOR . $k;
            if($itemColourSizeFlat->hasData($scopedValue))
            {
                $preparedAttributes[$v] = $itemColourSizeFlat->getData($scopedValue);
            }
        }

        if (self::ALLOW_PRICE_CHANGES_IN_CONFIGURABLE)
        {
            $preparedAttributes[self::MAGENTO_PRICE_FIELD]          = $this->_getFlatLowestPrice($websiteCode);
            $preparedAttributes[self::MAGENTO_SPECIAL_PRICE_FIELD]  = '';
            $preparedAttributes['highest_price']                    = $this->_getFlatHighestPrice($websiteCode);
        }
        else
        {
            $preparedAttributes = $this->_prepareDataSimpleFinalPrice($itemColourSizeFlat, $preparedAttributes, $websiteCode);
            $preparedAttributes['highest_price']                    = $this->_getFlatHighestPrice($websiteCode);
        }

        return $preparedAttributes;
    }

    /**
     * Prepare data for simple products.
     *
     * @param $itemColourSizeFlat
     * @param string $mode
     * @return mixed
     */
    protected function _prepareDataSimple ($itemColourSizeFlat, $mode = self::MODE_CREATE)
    {
        $preparedAttributes                 = $this->_prepareData($itemColourSizeFlat);
        $preparedAttributes                 = $this->_prepareDataSimpleFinalPrice($itemColourSizeFlat, $preparedAttributes);
        $preparedAttributes['type_id']      = Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
        $preparedAttributes['stock_data']   = array(
            'is_in_stock' => $itemColourSizeFlat->getQuantityAvailable() > 0 ? 1 : 0,
            'qty' => $itemColourSizeFlat->getQuantityAvailable(),
        );

        foreach ($this->_simpleProductNameUniqueness as $attribute => $originalAttribute)
        {
            if (!array_key_exists($attribute, $preparedAttributes))
            {
                continue;
            }

            // @TODO FIX THIS usingaccumulated data per product group..
            $preparedAttributes['name'] = $preparedAttributes['name'] .
                ' ' .
                $preparedAttributes[$attribute.self::LABEL_DATA_APPEND];
        }

        // needs to manually generate url key on Magento 1.13 due to bug.
//        $preparedAttributes['url_key'] = $this->_getProductModelSimple($this->_getDefaultWebsiteCode())
//            ->formatUrlKey($preparedAttributes['name']);
        
        $preparedAttributes['category_ids'] = array();

        return $preparedAttributes;
    }

    /**
     * Prepare data for configurable products.
     *
     * @param $itemColourSizeFlat
     * @param string $mode
     * @return array|mixed
     */
    protected function _prepareDataConfigurable ($itemColourSizeFlat, $mode = self::MODE_CREATE)
    {
        $preparedAttributes                             = $this->_prepareData($itemColourSizeFlat, $mode);
        $preparedAttributes['type_id']                  = Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE;
        $preparedAttributes['visibility']               = Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH;
        $preparedAttributes['sku']                      = $this->_calculateGroupSku($itemColourSizeFlat);

        if (self::ALLOW_PRICE_CHANGES_IN_CONFIGURABLE)
        {
            $preparedAttributes[self::MAGENTO_PRICE_FIELD]          = $this->_getFlatLowestPrice();
            $preparedAttributes[self::MAGENTO_SPECIAL_PRICE_FIELD]  = '';
            $preparedAttributes['highest_price']                    = $this->_getFlatHighestPrice();
        }
        else
        {
            $preparedAttributes = $this->_prepareDataSimpleFinalPrice($itemColourSizeFlat, $preparedAttributes);
            $preparedAttributes['highest_price'] = $this->_getFlatHighestPrice();
        }

        $preparedAttributes['name']                     = $preparedAttributes['name'] . ' ' . $itemColourSizeFlat->getBaseColourDescription();
        // $preparedAttributes['category_ids']             = $this->_getLastProcessedSimpleData(self::TYPE_LASTPROCESSED_CATEGORIES);

        $preparedAttributes['stock_data']   = array(
            'use_config_manage_stock' => 1,
            'use_config_enable_qty_increments' => 1,
            'is_in_stock' => $this->_getLastProcessedSimpleData(self::TYPE_LASTPROCESSED_STOCK),
        );

        // needs to manually generate url key on Magento 1.13 due to bug.
//        $preparedAttributes['url_key'] = $this->_getProductModelConfigurable()->formatUrlKey($preparedAttributes['name']);

        return $preparedAttributes;
    }

    /**
     * For configurable products that contains all the colours, it needs to have a unique URL based on its
     * name, for this purpose we gather together all colours being processed to add to the name of the
     * configurable. In this case, we use this method to calculate the configurable name based on
     * all simple product colours processed so far.
     *
     * This is not the case if every colour is a different configurable.
     *
     * @param $name
     * @return string
     */
    protected function _calculateGroupName ($name)
    {
        if ($this->_getLastProcessedSimpleDataAmount() > 1)
        {
            $name = $name . ' ' . $this->_getLastProcessedSimpleData(self::TYPE_LASTPROCESSED_NAMES);
        }
        return $name;
    }

    /**
     * The group SKU is the own product item_colour_code.
     *
     * @param $itemToProcess
     * @return mixed
     */
    protected function _calculateGroupSku ($itemToProcess)
    {
        return $itemToProcess->getData(self::CONFIGURABLE_GROUP_BY);
    }

    /**
     * Filter product attributes that are allowed to be updated.
     * Not all attributes need to be updated in Magento after they are changed in RMS
     * as some are overridden in Magento.
     *
     * @param $productData
     * @return array
     */
    protected function _prepareDataForUpdate($productData)
    {
        $filteredProductData = array();

        foreach($productData as $k => $v)
        {
            if (!in_array($k, $this->_allowUpdateAttributes))
            {
                continue;
            }

            $filteredProductData[$k] = $v;
        }

        return $filteredProductData;
    }

    /**
     * Actually process the prepared data and creates the configurable product
     * based on all simple products created so far.
     *
     * @TODO reorganize function is too long
     *
     * @param $itemToProcess A sample row from the just processed single row.
     * @return $this
     */
    protected function _processConfigurableSku($itemToProcess)
    {
        $singleArray    = $this->_getLastProcessedSimpleData(self::TYPE_LASTPROCESSED_CONFIGURABLE_SIMPLE_DATA);
        $attributeArray = $this->_getLastProcessedSimpleData(self::TYPE_LASTPROCESSED_CONFIGURABLE_ATTRIBUTE_DATA);

        foreach ($this->_getFlatProductCollection() as $itemColourSizeFlat)
        {
            $groupSku = $this->_calculateGroupSku($itemToProcess);
            
            try
            {
                $productModel   = $this->_getProductModelConfigurable($this->_getDefaultWebsiteCode());
                $productId      = $productModel->clearInstance()->reset()->getResource()->getIdBySku($groupSku);
                $productData    = $this->_prepareDataConfigurable($itemColourSizeFlat);

                $mode = self::MODE_CREATE;
                if ($productId > 0)
                {
                    $mode = self::MODE_UPDATE;
                    $productModel->load($productId);
                    
                    $productData = $this->_prepareWebsiteCategory($productData, null, $productModel);
                    $productData = $this->_prepareDataForUpdate($productData);
                    $productData = $this->_prepareFixMagentoBugs($productData, $productModel);

                    // Load already existent associated products structure
                    // to merge ids into the new structure. This loads a collection
                    // from the super attribute table.
                    $currentAttributeArray = $productModel->getTypeInstance(true)->getConfigurableAttributesAsArray($productModel);

                    foreach ($attributeArray as &$attributeToUpdate)
                    {
                        foreach($currentAttributeArray as $currentAttribute)
                        {
                            if ($currentAttribute['attribute_code'] == $attributeToUpdate['attribute_code'])
                            {
                                $attributeToUpdate['id'] = $currentAttribute['id'];
                                $attributeToUpdate['use_default'] = $currentAttribute['use_default'];
                                $attributeToUpdate['position'] = $currentAttribute['position'];
                            }
                        }
                    }
                }
                
                if ($mode == self::MODE_CREATE)
                {
                    $productModel
                        ->addData($productData)
                        ->setCanSaveConfigurableAttributes(1)
                        ->setConfigurableProductsData($singleArray)
                        ->setConfigurableAttributesData($attributeArray);

                    $productModel->save();

                    // Loads generated id in case of new product
                    $productId = $productModel->getId();
                }

                // Saves "per scope" attributes
                foreach($this->_getWebsitesData() as $websiteCode => $isDefault)
                {
                    if ($isDefault) continue;

                    $productWebsiteData[$websiteCode] = $this->_prepareDataConfigurableScoped($itemColourSizeFlat, $websiteCode);

                    $productWebsiteModel = $this->_getProductModelConfigurable($websiteCode);
                    $productWebsiteModel
                        ->setStoreId($this->_getDefaultStoresByWebsite($websiteCode))
                        ->load($productId);
                    
                    if ($mode == self::MODE_UPDATE)
                    {
                        // checks if product got on sale or left on sale on this scope
                        $productData = $this->_prepareWebsiteCategory($productData, $websiteCode, $productWebsiteModel);

                        // fix core magento bugs
                        $productWebsiteData[$websiteCode] = $this->_prepareFixMagentoBugs($productWebsiteData[$websiteCode], $productWebsiteModel);
                    }
                }
                
                if ($mode == self::MODE_UPDATE)
                {
                    $productModel
                        ->addData($productData)
                        ->setCanSaveConfigurableAttributes(1)
                        ->setConfigurableProductsData($singleArray)
                        ->setConfigurableAttributesData($attributeArray);
                    
                    $productModel->save();
                }

                foreach($this->_getWebsitesData() as $websiteCode => $isDefault)
                {
                    if ($isDefault) continue;
                    
                    $productWebsiteModel = $this->_getProductModelConfigurable($websiteCode);
                    
                    $productWebsiteModel->clearInstance()
                        ->reset()
                        ->load($productId)
                        ->setStoreId($this->_getDefaultStoresByWebsite($websiteCode))
                    ;
                    
                    // Specify different data in this website/store scope.
                    // For some reason in this store scope scenario just "setData" or
                    // "addData" doesn't work.
                    foreach ($productWebsiteData[$websiteCode] as $key => $value)
                    {
                        if (array_key_exists($key, $productData) && $productWebsiteData[$websiteCode][$key] == $productData[$key])
                        {
                            // set as "use default"
                            $productWebsiteModel->setData($key, false);
                        }
                        else
                        {
                            $productWebsiteModel->setData($key, $value);
                        }
                    }

                    $attributes = $productWebsiteModel->getTypeInstance(true)
                        ->getConfigurableAttributesAsArray($productWebsiteModel);

                    if(!$attributes)
                    {
                        Mage::throwException("Configurable product association not found.");
                        break;
                    }
                    else
                    {
                        // Reorganize product association
                        // in a per-scope perspective (prices can be different between stores)
                        // and in case the configurable have different prices this needs to be catered.

                        $attributeArray = $this->_getLastProcessedSimpleData(
                            $websiteCode . self::SCOPE_SEPARATOR . self::TYPE_LASTPROCESSED_CONFIGURABLE_ATTRIBUTE_DATA
                        );

                        $i = 0;
                        foreach($attributes as &$attribute)
                        {
                            if (isset($attribute['values']) && is_array($attribute['values']))
                            {
                                foreach ($attribute['values'] as &$attributeValue)
                                {
                                    if (!array_key_exists($i, $attributeArray))
                                    {
                                        continue;
                                    }

                                    foreach($attributeArray[$i]['values'] as $attributeScopeValue)
                                    {
                                        if ($attributeValue['value_index'] == $attributeScopeValue['value_index'])
                                        {
                                            $attributeValue['use_default_value'] = true;

                                            if ($attributeValue['pricing_value'] != $attributeScopeValue['pricing_value'] &&
                                                $this->_configurableAttributes[$attribute['attribute_code']] === true)
                                            {
                                                if (array_key_exists("value_id", $attributeValue))
                                                {
                                                    unset($attributeValue["value_id"]);
                                                }

                                                // updates the price in the existing array, as the existing
                                                // array contains the super_attribute ids
                                                $attributeValue['use_default_value'] = false;
                                                $attributeValue['pricing_value'] = $attributeScopeValue['pricing_value'];
                                            }
                                        }
                                    }
                                }
                            }
                            $i++;
                        }

                        // Update prices into new model for the specific scope
                        $productWebsiteModel
                            ->setConfigurableAttributesData($attributes);
                        
                        if ($mode == self::MODE_CREATE)
                        {
                            $productWebsiteModel->save();
                        }
                    }
                }
                
                // save all product models, one per scope
                if ($mode == self::MODE_UPDATE)
                {
                    foreach($this->_getWebsitesData() as $websiteCode => $isDefault)
                    {
                        if ($isDefault)
                        {
                            continue;
                        }
						
                        $this->_getProductModelConfigurable($websiteCode)
                            ->setUrlKey(false)
                            ->save();
                    }
                }
                
                foreach($this->_getWebsitesData() as $websiteCode => $isDefault)
                {
                    $this->_getProductModelConfigurable($websiteCode)->clearInstance();
                }
                
                $this->_countForReport['success']++;
                $this->_log(array("Configurable product",$groupSku,"{$mode}d successfully."));
                
                // just one iteration is enough to create the configurable product
                // with the flat data available at hand
                break;
            }
            catch (Exception $e)
            {
                $this->_countForReport['error']++;
                $this->_log(array("Configurable product",$groupSku,"had an ERROR.",$e->getMessage()));
                
                // just one iteration is enough to create the configurable product
                // with the flat data available at hand
                break;
            }
        }

        return $this;
    }

    /**
     * In case prices can be different in configurables, we need to set the configurable
     * price with the lowes value possible, and then adjust the differences within
     * any configurable product.
     *
     * This method calculates the lowest price in a product.
     *
     * e.g.:    product base price  = 10  (lowest price)
     *          brown colour        = +0  (10 dollars)
     *          blue colour         = +0  (10 dollars)
     *          pink colour         = +10 (20 dollars)
     *
     * Not used in case colours are divisible in different configurables and
     * price change within a configurable product is not allowed.
     *
     * @param null $websiteCode Scope we are calculating the price for, as prices can be different per website.
     * @return null
     */
    protected function _getFlatLowestPrice($websiteCode = null)
    {
        $lowestPrice = null;

        if ($websiteCode == null)
        {
            $field = self::FLAT_PRICE_FIELD;
        }
        else
        {
            $field = $websiteCode . self::SCOPE_SEPARATOR . self::FLAT_PRICE_FIELD;
        }

        foreach ($this->_getFlatProductCollection() as $itemColourSizeFlat)
        {
            if (is_null($lowestPrice))
            {
                $lowestPrice = $itemColourSizeFlat->getData($field);
            }
            $lowestPrice = $itemColourSizeFlat->getData($field) > $lowestPrice ? $lowestPrice : $itemColourSizeFlat->getData($field);
        }

        return $lowestPrice;
    }

    /**
     * This method calculates the highest possible price of a product.
     *
     * e.g.:    product base price  = 10  (lowest price)
     *          brown colour        = +0  (10 dollars)
     *          blue colour         = +0  (10 dollars)
     *          pink colour         = +10 (20 dollars - highest price possible)
     *
     * Not used in case colours are divisible in different configurables and
     * price change within a configurable product is not allowed.
     *
     * @param null $websiteCode Scope we are calculating the price for, as prices can be different per website.
     * @return int
     */
    protected function _getFlatHighestPrice($websiteCode = null)
    {
        $highestPrice = 0;

        if ($websiteCode == null)
        {
            $currentField   = self::FLAT_PRICE_FIELD;
            $rrpField       = self::FLAT_RRP_FIELD;
        }
        else
        {
            $currentField   = $websiteCode . self::SCOPE_SEPARATOR . self::FLAT_PRICE_FIELD;
            $rrpField       = $websiteCode . self::SCOPE_SEPARATOR . self::FLAT_RRP_FIELD;
        }

        foreach ($this->_getFlatProductCollection() as $itemColourSizeFlat)
        {
            $localHighest = $itemColourSizeFlat->getData($rrpField) > $itemColourSizeFlat->getData($currentField) ? $itemColourSizeFlat->getData($currentField) : $itemColourSizeFlat->getData($rrpField);
            $highestPrice = $localHighest > $highestPrice ? $localHighest : $highestPrice;
        }

        return $highestPrice;
    }

    /**
     * Check if product is on sale on the API data for a specific website.
     * The rule for a product to be on sale is rrp > current_price.
     *
     * @param null $websiteCode
     * @return bool
     */
    protected function _getFlatIsOnSale($websiteCode = null)
    {
        $rrpField = null;
        $currentPriceField = null;

        if ($websiteCode == null)
        {
            $rrpField           = self::FLAT_RRP_FIELD;
            $currentPriceField  = self::FLAT_PRICE_FIELD;
        }
        else
        {
            $rrpField           = $websiteCode . self::SCOPE_SEPARATOR . self::FLAT_RRP_FIELD;
            $currentPriceField  = $websiteCode . self::SCOPE_SEPARATOR . self::FLAT_PRICE_FIELD;
        }

        foreach ($this->_getFlatProductCollection() as $itemColourSizeFlat)
        {
            if ($itemColourSizeFlat->getData($rrpField) > $itemColourSizeFlat->getData($currentPriceField))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if product is on sale on Magento.
     * At this point the model is already loaded for a specific website if necessary.
     *
     * The rule for a product to be on sale in MAGENTO is if there is a special_price.
     *
     * @param $productModel
     * @return bool
     */
    protected function _getProductIsOnSale($productModel)
    {
        if (($productModel->getData('special_price') != 0) && ($productModel->getData('special_price') > 0))
        {
            return true;
        }

        return false;
    }

    /**
     * Get all categories for a specific website. This is necessary when moving a product
     * away from the current categories for this website and put it in the "on sale"
     * category automatically. since the category tree for a product is the same on all scopes
     * we need to filter out the current categories, checking one by one if they are from the
     * website of the product entering on sale.
     * 
     * 
     * IMPORTANT:
     * Please note that it is expected that websites
     * have different root category IDs assigned to them
     * in the backend. This makes the category assignment and movements to
     * be calculated in a per website basis. It may still work with several
     * websites sharing one root category id, however then category movement
     * will only work for that category tree.
     * 
     *
     * @param $websiteCode
     * @return mixed
     */
    protected function _getWebsiteCategoryGroups($websiteCode)
    {
        $rootId = null;

        if (!array_key_exists($websiteCode, $this->_websiteCategoryGroups))
        {
            $rootId = $this->_getDefaultStoresByWebsite($websiteCode, true)->getRootCategoryId();

            $category = Mage::getModel('catalog/category')->load($rootId);
            $allChildren = $category->getAllChildren(true);

            $this->_websiteCategoryGroups[$websiteCode] = $allChildren;
        }

        return $this->_websiteCategoryGroups[$websiteCode];
    }

    /**
     * Remove all current categories from a product for the specific website
     * the product went on sale ON.
     *
     * Adds the product into the backend defined "on sale" category.
     *
     * Records all current removed categories into the previous_categories_ids
     * attribute to be restored later.
     *
     * @param $productData Current processed product attributes to be added to Magento.
     * @param $websiteCode Current scope that the product went on sale.
     * @return array
     */
    protected function _prepareWebsiteCategoryAddOnSale($productData, $websiteCode)
    {
        if (self::ALLOW_CATEGORY_MOVEMENT)
        {
            $categoryIds = $productData['category_ids'];
            $previousCategoryIds = strlen($productData['previous_categories_ids']) > 0
                ? explode(',', $productData['previous_categories_ids'])
                : array();

            $this->_getWebsiteCategoryGroups($websiteCode);

            // remove from current categories
            foreach ($categoryIds as $k => $id)
            {
                if (in_array($id, $this->_getWebsiteCategoryGroups($websiteCode)))
                {
                    if (!in_array($id, $previousCategoryIds))
                    {
                        array_push($previousCategoryIds, $id);
                    }
                    unset($categoryIds[$k]);
                }
            }

            $productData['category_ids'] = $categoryIds;
            $productData['previous_categories_ids'] = implode(',',$previousCategoryIds);
        }
        
        // move it on sale
        $productData['category_ids'] = array_merge(
            $productData['category_ids'],
            $this->_getOnSaleCategories($websiteCode)
        );

        return $productData;
    }

    /**
     * Removes the product from the "on sale" category.
     *
     * Restores all previous_categories_ids categories into the product category tree for
     * the specific website where the product is getting back from sale.
     *
     * Clears the previous_categories_ids attribute removing categories from the
     * currently being processed website for this product.
     *
     * @param $productData Current processed product attributes to be added to Magento.
     * @param $websiteCode Current scope that the product went on sale.
     * @return array
     */
    protected function _prepareWebsiteCategoryRemoveFromSale($productData, $websiteCode)
    {
        $categoryIds = $productData['category_ids'];
        $onSaleCategoriesArray = $this->_getOnSaleCategories($websiteCode);
        
        // remove from sale
        foreach ($categoryIds as $k => $id)
        {
            if ( in_array($id, $onSaleCategoriesArray) )
            {
                unset($categoryIds[$k]);
            }
        }
        
        if (self::ALLOW_CATEGORY_MOVEMENT)
        {
            // restore previous categories
            $categoryTree = $this->_getWebsiteCategoryGroups($websiteCode);
            $previousCategories = explode(',', $productData['previous_categories_ids']);

            foreach($previousCategories as $k => $category)
            {
                if (in_array($category, $categoryTree))
                {
                    if (!in_array($category, $categoryIds))
                    {
                        array_push(
                            $categoryIds,
                            $category
                        );
                    }
                    unset($previousCategories[$k]);
                }
            }
            $productData['previous_categories_ids'] = implode(',',$previousCategories);
        }
        
        $productData['category_ids'] = $categoryIds;

        return $productData;
    }

    /**
     * Check if product went "on sale" or is removed from the "on sale"
     * and process the data accordingly.
     *
     * It takes into account the scope, as product can get on sale in a website,
     * but not on the other.
     *
     * This function is expected to be called on a per website basis.
     *
     * @param $productData Current processed product attributes to be added to Magento.
     * @param $websiteCode Current scope being processed.
     * @param $productModel Current product existing in Magento to be updated (already loaded by scope).
     * @return array
     */
    protected function _prepareWebsiteCategory($productData, $websiteCode = null, $productModel = null)
    {
        // if this is the default website we get the data from the current
        // product inside Magento
        if ($websiteCode == null)
        {
            $productData['category_ids']            = $productModel->getCategoryIds();
            $productData['previous_categories_ids'] = $productModel->getData('previous_categories_ids');
            $processedWebsiteCode = $this->_getDefaultWebsiteCode();
        }
        else
        {
            $processedWebsiteCode = $websiteCode;
        }

        // We don't move core products from one category to another.
        // Core products 'is_noncore' == 0' so we keep categories as they are
        if ($productData['is_noncore'] == 0 ||
            // We also don't move simple products as per BNT-1166
            $productModel->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
        {
            return $productData;
        }

        // Move products around, per scope calculation
        if ($this->_getProductIsOnSale($productModel) && !$this->_getFlatIsOnSale($websiteCode))
        {
            $productData = $this->_prepareWebsiteCategoryRemoveFromSale($productData, $processedWebsiteCode);
        }
        else if (!$this->_getProductIsOnSale($productModel) && $this->_getFlatIsOnSale($websiteCode))
        {
            $productData = $this->_prepareWebsiteCategoryAddOnSale($productData, $processedWebsiteCode);
        }

        return $productData;
    }
    
    /**
     * Calculates lowest price possible at the single product level.
     * Used when generating the differencies for specific colours that are
     * associated products into a configurable product.
     *
     * Not used if colours are generated as different configurables
     * in Magento.
     *
     * @param $productData
     * @return mixed
     */
    protected function _calculateSingleLowestPrice($productData)
    {
        if (array_key_exists(self::MAGENTO_SPECIAL_PRICE_FIELD, $productData))
        {
            return $productData[self::MAGENTO_SPECIAL_PRICE_FIELD];
        }

        return $productData[self::MAGENTO_PRICE_FIELD];
    }

    /**
     * Magento 1.13.0x has a bug regarding dealing with date ranges.
     * The date range gets the current date on the "from" date if the
     * special price is filled.
     *
     * Except if we set a "false" value on it. (??)
     * Bug was reported.
     *
     * @param $productData
     * @param $productModel
     * @return mixed
     */
    protected function _prepareFixMagentoBugs($productData, $productModel)
    {
        $productData['news_from_date'] =
            $productModel->getData('news_from_date') != null &&
                $productModel->getData('news_from_date') != ''
                ? $productModel->getData('news_from_date')
                : false;

        $productData['custom_design_from'] =
            $productModel->getData('custom_design_from') != null &&
                $productModel->getData('custom_design_from') != ''
                ? $productModel->getData('custom_design_from')
                : false;

        return $productData;
    }

    /**
     * This method prepares the array that are set into a configurable product to associate
     * simple products to it.
     *
     * This is a overcomplex array, that needs to be created exactly as is expected from Magento.
     * For an example of the array, we can associate a few products with a configurable in the backend,
     * then save and read the request array that was sent to the controller.
     *
     * This array is created iteratively, having this method being called every time we create
     * a simple product. We create all simple products first and keep accumulating data, until
     * we then finally generated the configurable with the associations and flush all data away.
     *
     * @param $productData
     * @param $productId
     * @param null $websiteCode
     * @param null $productScopedData
     */
    protected function _prepareDataConfigurableOptions($productData, $productId, $websiteCode = null, $productScopedData = null)
    {
        $j = 0;

        if ($websiteCode != null)
        {
            $singleArrayKey     = $websiteCode . self::SCOPE_SEPARATOR . self::TYPE_LASTPROCESSED_CONFIGURABLE_SIMPLE_DATA;
            $attributeArrayKey  = $websiteCode . self::SCOPE_SEPARATOR . self::TYPE_LASTPROCESSED_CONFIGURABLE_ATTRIBUTE_DATA;
            $addedIdKey         = $websiteCode . self::SCOPE_SEPARATOR . self::TYPE_LASTPROCESSED_CONFIGURABLE_ADDED_ID;
        }
        else
        {
            $singleArrayKey     = self::TYPE_LASTPROCESSED_CONFIGURABLE_SIMPLE_DATA;
            $attributeArrayKey  = self::TYPE_LASTPROCESSED_CONFIGURABLE_ATTRIBUTE_DATA;
            $addedIdKey         = self::TYPE_LASTPROCESSED_CONFIGURABLE_ADDED_ID;
        }

        // load previous processed data in previous iterations
        $singleArray    = $this->_getLastProcessedSimpleData($singleArrayKey);
        $attributeArray = $this->_getLastProcessedSimpleData($attributeArrayKey);
        $addedId        = $this->_getLastProcessedSimpleData($addedIdKey);

        foreach ($this->_configurableAttributes as $attribute => $isPriceLevel)
        {
            if (!array_key_exists($attribute, $productData))
            {
                continue;
            }

            $valueIndex             = $productData[$attribute];
            $attributePriceArray    = array();
            $calculatedPrice = '';

            if (self::ALLOW_PRICE_CHANGES_IN_CONFIGURABLE)
            {
                if ($websiteCode != null)
                {
                    $calculatedPrice    = $this->_calculateSingleLowestPrice($productScopedData) - $this->_getFlatLowestPrice($websiteCode);
                }
                else
                {
                    $calculatedPrice    = $this->_calculateSingleLowestPrice($productData) - $this->_getFlatLowestPrice();
                }
            }

            if ($isPriceLevel && !array_key_exists($valueIndex, $addedId))
            {
                /*
                 * Please make sure option on configuration xpath catalog/price/catalog_price_scope
                 * is set to website, otherwise we just have a global product price.
                 *
                 * Also Magento NZ$ to AU$ needs to 1=1.
                 */
                $attributePriceArray    = array(
                    'is_percent'    => 0,
                    'pricing_value' => $calculatedPrice,
                );
            }
            if (!$isPriceLevel)
            {
                $attributePriceArray    = array(
                    'is_percent'    => 0,
                    'pricing_value' => '',
                );
            }

            if (!array_key_exists($j, $attributeArray))
            {
                $attributeArray[$j]                     = array();
                $attributeArray[$j]                     = array(
                    'id'            => null,
                    'label'         => $this->_getAttributeModel($attribute)->getFrontendLabel(),
                    'use_default'   => null,
                    'position'      => null,
                );
                $attributeArray[$j]['values']           = array();
                $attributeArray[$j]['attribute_id']     = $this->_getAttributeId($attribute);
                $attributeArray[$j]['attribute_code']   = $attribute;
                $attributeArray[$j]['frontend_label']   = $this->_getAttributeModel($attribute)->getFrontendLabel();
                $attributeArray[$j]["store_label"]      = $this->_getAttributeModel($attribute)->getFrontendLabel();
                $attributeArray[$j]['html_id']          = 'configurable__attribute_'.$j;
            }

            if (!array_key_exists($valueIndex, $addedId))
            {
                $attributeArray[$j]['values'][]         = array(
                    'label'         => $productData[$attribute.self::LABEL_DATA_APPEND],
                    'attribute_id'  => $this->_getAttributeId($attribute),
                    'value_index'   => $valueIndex,
                    'is_percent'    => 0,
                    'pricing_value' => $isPriceLevel ? $calculatedPrice : '',
                );
            }

            if (!array_key_exists($productId, $singleArray))
            {
                $singleArray[$productId] = array();
            }
            if (!is_array($singleArray[$productId]))
            {
                $singleArray[$productId] = array();
            }
            $singleArray[$productId][$j]  = array_merge(
                array(
                    'label'         => $productData[$attribute.self::LABEL_DATA_APPEND],
                    'attribute_id'  => $this->_getAttributeId($attribute),
                    'value_index'   => $valueIndex
                ),
                $attributePriceArray
            );

            if (!array_key_exists($valueIndex, $addedId))
            {
                $addedId[$valueIndex]   = true;
            }

            $j++;
        }

        // save iteration
        $this->_setLastProcessedSimpleData($singleArrayKey, $singleArray);
        $this->_setLastProcessedSimpleData($attributeArrayKey, $attributeArray);
        $this->_setLastProcessedSimpleData($addedIdKey, $addedId);
    }

    /**
     * Calls data preparation methods and generates a simple product into Magento.
     * Data from this method gets accumulated on each interaction to generate the configurable.
     *
     * @param $itemToProcess
     * @return $this
     */
    protected function _processSimpleSkus($itemToProcess)
    {
        // if one simple product is in stock the configurable
        // will be later marked as in stock as well.
        $isGroupInStock = 0;
        $i              = 0;

        foreach ($this->_getFlatProductCollection() as $itemColourSizeFlat)
        {
            try
            {
                // check if it is on sale
                $appendType = $itemColourSizeFlat->getRrp() > $itemColourSizeFlat->getCurrentPrice() ?
                    self::TYPE_LASTPROCESSED_ONSALE_DATA : self::TYPE_LASTPROCESSED_CONFIGURABLE_SIMPLE_DATA;

                $productModel   = $this->_getProductModelSimple($this->_getDefaultWebsiteCode());
                $productId      = $productModel->clearInstance()
                    ->reset()
                    ->getResource()
                    ->getIdBySku($itemColourSizeFlat->getSellCodeCode());

                $productData = $this->_prepareDataSimple($itemColourSizeFlat);

                $mode = self::MODE_CREATE;
                if ($productId > 0)
                {
                    $mode = self::MODE_UPDATE;
                    $productModel->load($productId);

                    // checks if product got on sale or left on sale on the default scope
                    $productData = $this->_prepareWebsiteCategory($productData, null, $productModel);

                    // remove attributes that are not allowed to be updated into Magento
                    $productData = $this->_prepareDataForUpdate($productData);

                    // fix core magento bugs
                    $productData = $this->_prepareFixMagentoBugs($productData, $productModel);
                }

                // check if configurable should be in stock or not
                if ($productData['stock_data']['is_in_stock'] > 0)
                {
                    $isGroupInStock = $productData['stock_data']['is_in_stock'];
                }

                // Save magento product $productModel with default data
                if ($mode == self::MODE_CREATE)
                {
                    $productModel->addData($productData);
                    $productModel->save();
                    $productId = $productModel->getId();
                }
                
                $productWebsiteData = array();

                // Loads per scope price/other scoped data
                foreach($this->_getWebsitesData() as $websiteCode => $isDefault)
                {
                    if ($isDefault) continue;

                    $productWebsiteData[$websiteCode] = $this->_prepareDataSimpleScoped($itemColourSizeFlat, $websiteCode);

                    // Here we load the product by setting the store id BEFORE the load, as then loaded data will be from
                    // the specific store id mentioned. Otherwise the loaded data would be from the default scope.
                    // Please notice we CAN'T then just change the data and save it back
                    // otherwise all data from the products will have the "use default value" unmarked for this specific
                    // store, even if the value is the same from the default value. This will make it hard, for an example
                    // to change a product name in the backend, as it will need to be changed on all scopes. To fix this
                    // we need to load the product data again WITHOUT setting the store id first, and then setting the store
                    // id before saving.
                    $productWebsiteModel = $this->_getProductModelSimple($websiteCode);
                    $productWebsiteModel->clearInstance()
                        ->reset()
                        ->setStoreId($this->_getDefaultStoresByWebsite($websiteCode))
                        ->load($productId)
                    ;

                    if ($mode == self::MODE_UPDATE)
                    {
                        // checks if product got on sale or left on sale on this scope
                        $productData = $this->_prepareWebsiteCategory($productData, $websiteCode, $productWebsiteModel);

                        // fix core magento bugs
                        $productWebsiteData[$websiteCode] = $this->_prepareFixMagentoBugs($productWebsiteData[$websiteCode], $productWebsiteModel);
                    }
                }
                
                // save all product models, one per scope
                if ($mode == self::MODE_UPDATE)
                {
                    $this->_getProductModelSimple($this->_getDefaultWebsiteCode())->addData($productData)->save();
                }

                // Saves per scope price/other scoped data
                foreach($this->_getWebsitesData() as $websiteCode => $isDefault)
                {
                    if ($isDefault) continue;
                    
                    $productWebsiteModel = $this->_getProductModelSimple($websiteCode);
                    
                    // Loading the data again as a fix to not change the "use default values" flag on data that is the same
                    // from the default value.
                    // At this point we finished all product data analysis and we will be just saving.
                    // Unfortunately this makes the process slower, but it is the difference from loading scoped product
                    // attributes from saving it back.
                    $productWebsiteModel->clearInstance()
                        ->reset()
                        ->load($productId)
                        ->setStoreId($this->_getDefaultStoresByWebsite($websiteCode))
                    ;

                    // For some reason in this store scope scenario just "setData" or
                    // "addData" doesn't work.
                    foreach ($productWebsiteData[$websiteCode] as $key => $value)
                    {
                        if (array_key_exists($key, $productData) && ($productWebsiteData[$websiteCode][$key] == $productData[$key]))
                        {
                            // set as "use default"
                            $productWebsiteModel->setData($key, false);
                        }
                        else
                        {
                            $productWebsiteModel->setData($key, $value);
                        }
                    }

                    // accumulates associative data to be added into the configurable later
                    $this->_prepareDataConfigurableOptions($productData, $productId, $websiteCode, $productWebsiteData[$websiteCode]);

                    if ($mode == self::MODE_CREATE)
                    {
                        $productWebsiteModel->save();
                    }
                }

                // save all product models, one per scope
                if ($mode == self::MODE_UPDATE)
                {
                    foreach($this->_getWebsitesData() as $websiteCode => $isDefault)
                    {
                        if ($isDefault)
                        {
                            continue;
                        }
						
                        $this->_getProductModelSimple($websiteCode)
                            ->setUrlKey(false)
                            ->save();
                    }
                }

                foreach($this->_getWebsitesData() as $websiteCode => $isDefault)
                {
                    $this->_getProductModelSimple($websiteCode)->clearInstance();
                }

                /*
                 * And update the "translated_at" attribute on the flat table.
                 * So this product doesn't get translated again on the next iteration.
                 */
                $itemColourSizeFlat->setTranslatedAt($this->_getFlatProductModel()
                    ->getResource()->formatDate(Mage::app()->getLocale()->date(), true))->save();

                $this->_prepareDataConfigurableOptions($productData, $productId);
                
                $this->_countForReport['success']++;
                $this->_log(array("Simple product",$itemColourSizeFlat->getSellCodeCode(),"{$mode}d successfully."));
            }
            catch (Exception $e)
            {
                $this->_countForReport['error']++;
                $this->_log(array("Simple product",$itemColourSizeFlat->getSellCodeCode(),"had an ERROR.",$e->getMessage()));
            }
        }

        $this->_setLastProcessedSimpleData(self::TYPE_LASTPROCESSED_STOCK, $isGroupInStock);
        $this->_setLastProcessedSimpleData(self::TYPE_LASTPROCESSED_CATEGORIES, $productData['category_ids']);

        return $this;
    }

    /**
     * Process a product "group".
     *
     * If products are being grouped per colour (e.g. 1 configurable per colour)
     * then we processes all sizes for that colour at once, clear all acumulated data and
     * jump into the next colour of this product.
     *
     * If products are bwing grouped per item_code (master level) and we have once configurable
     * with all colours and sizes, then all colours and sizes are processed togheter as a group.
     *
     * @param $itemToProcess
     */
    protected function _processProductGroup($itemToProcess)
    {
        $this->_updateFlatProductCollection($itemToProcess)
            ->_clearLastProcessedSimpleData();
        
        // check if the item is blacklisted
        $resultBlacklist = $this->_validateIsBlacklisted($itemToProcess);
        if (is_string($resultBlacklist))
        {
            $this->_log(array("Item {$resultBlacklist} ignored/blacklisted."));
            return;
        }
        
        // validate item
        try
        {
            $this->_validate($itemToProcess);
        }
        catch (Exception $e)
        {
            $this->_countForReport['error']++;
            $this->_log(array('ERROR:',$e->getMessage()));
            return;
        }
        
        // ok to go
        $this->_processSimpleSkus($itemToProcess)
            ->_processConfigurableSku($itemToProcess);
    }
    
    protected function _validateIsBlacklisted($itemToProcess)
    {
        if (null == $this->_blacklist)
        {
            $this->_blacklist = explode(",", Mage::getStoreConfig(self::CONFIG_PATH_CORE_SKU_BLACKLIST));
        }
        
        foreach ($this->_getFlatProductCollection() as $itemColourSizeFlat)
        {
            $prepareMessage =
                    "item_colour_ref = " .
                    $itemColourSizeFlat->getData('item_colour_ref');
            
            if (in_array($itemColourSizeFlat->getData('item_colour_ref'), $this->_blacklist))
            {
                return $prepareMessage;
            }
        }
        
        return false;
    }
    
    /**
     * Validation step added as to prevent bogus items to be created.
     * It may be the case not all expected data is filled on the ERP end.
     * So at this point we only go ahead if the extremely basic data exists.
     *
     * @param $itemToProcess
     */
    protected function _validate($itemToProcess)
    {
        foreach ($this->_getFlatProductCollection() as $itemColourSizeFlat)
        {
            $prepareMessage =
                    "sell_code_code = " .
                    $itemColourSizeFlat->getData('sell_code_code') .
                    ", item_colour_ref = " .
                    $itemColourSizeFlat->getData('item_colour_ref') .
                    ", item_code = " .
                    $itemColourSizeFlat->getData('item_code')
                    ;
            
            if (
                    strlen($itemColourSizeFlat->getData('sell_code_code')) <= 0
            )
            {
                Mage::throwException("Field sell_code_code is empty on ($prepareMessage)");
            }
            
            if (
                    strlen($itemColourSizeFlat->getData('item_colour_ref')) <= 0
            )
            {
                Mage::throwException("Field item_colour_ref is empty on ($prepareMessage)");
            }
            
            if (
                    strlen($itemColourSizeFlat->getData('item_code')) <= 0
            )
            {
                Mage::throwException("Field item_code is empty on ($prepareMessage)");
            }
                        
            if (
                    strlen($itemColourSizeFlat->getData('base_colour_description')) <= 0
            )
            {
                Mage::throwException("Field base_colour_description is empty on ($prepareMessage)");
            }
                        
            if (
                    strlen($itemColourSizeFlat->getData('base_colour_name')) <= 0
            )
            {
                Mage::throwException("Field base_colour_name is empty on ($prepareMessage)");
            }

            if (
                    strlen($itemColourSizeFlat->getData('base_colour_name')) <= 0
            )
            {
                Mage::throwException("Field base_colour_name is empty on ($prepareMessage)");
            }
        }
    }

    /**
     * Generates lazy loaded SQL query that retrieves all flat product (API data)
     * rows we will update into magento for a specific item_code or item_colour_code
     * which is the CONFIGURABLE_GROUP_BY constant.
     *
     * @param $itemToProcess
     * @return $this
     */
    protected function _updateFlatProductCollection($itemToProcess)
    {
        $this->_getFlatProductCollection()
            ->clear()
            ->getSelect()
            ->reset(Zend_Db_Select::WHERE)
            ->reset(Zend_Db_Select::GROUP);

        $this->_getFlatProductCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter(self::CONFIGURABLE_GROUP_BY, $itemToProcess->getData(self::CONFIGURABLE_GROUP_BY));

        return $this;
    }

    /**
     * Gets list of products that will need to be updated and process each group.
     * 
     * @return null
     */
    protected function _processList()
    {
        foreach($this->_getFlatProductItemCodesToTranslate() as $item)
        {
            $this->_processProductGroup($item);
        }
    }

    /**
     * Entry point for the shell command.
     */
    protected function _update($date = null)
    {
        
        $this->_log(array($this->_jobId, "Starting product translation"));

        /*
         * IMPORTANT NOTES ON MEMORY USAGE:
         *
         * The catalog/product save command on this script is accumulating memory on each product save
         * as an observer attached to it keeps creating model. It is a core Magento observer
         * Enterprise_CatalogSearch_Model_Observer::processProductSaveEvent.
         * This event runs a enterprise_catalogsearch/index_action_fulltext_refresh.
         *
         * The command is used to create a new product in Magento with data coming from Retail Directions.
         * This $this->_log method uses Magento::log, and adds memory statistics to the log.
         *
         * In this core Magento observer, a factory approach is used, and a new model is created on each
         * product for reindex. Magento is keeping a reference for each created model so, on each
         * interaction, memory usage is growing.
         *
         * I assume it is a Magento bug, as I don't see a need for this to happen. If the Full Text Index is
         * turned off before the script runs, memory is not accumulated within this script. Example is below:
         *
         * $config = Mage::app()->getConfig();
         * $config->saveConfig(Enterprise_CatalogSearch_Model_Observer::XML_PATH_LIVE_FULLTEXT_REINDEX_ENABLED, "0");
         */
        
        try
        {
            $this->_processList();
        }
        catch (Exception $e)
        {
            $this->_countForReport['error']++;
            $this->_log(array('ERROR:',$e->getMessage()));
        }
        
        $this->_log(array(
            $this->_jobId,
            "Finish product translation with successes:",
            $this->_countForReport['success'],
            "and errors:",
            $this->_countForReport['error']
        ));
    }
}
