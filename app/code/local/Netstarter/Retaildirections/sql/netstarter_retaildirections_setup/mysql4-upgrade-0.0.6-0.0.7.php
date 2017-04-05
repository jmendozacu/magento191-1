<?php

$installer = Mage::getResourceModel('catalog/setup','core_setup');
$installer->startSetup();

$installer->addAttribute('catalog_product', 'highest_price', array(
    'type'              => 'decimal',
    'input'             => 'price',
    'backend'            => 'catalog/product_attribute_backend_price',
    'frontend'          => '',
    'source'            => '',
    'label'             => 'Highest Price',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'required'          => '0',
    'user_defined'      => '1',
    'searchable'        => '0',
    'filterable'        => '0',
    'comparable'        => '0',
    'apply_to' => implode(',',
        array(
            Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
        )
    ),
    'visible_on_front'  => '1',
    'visible_in_advanced_search' => '0',
    'unique'            => '0'
));

$installer->addAttribute('catalog_product', 'item_code', array(
    'type'              => 'varchar',
    'input'             => 'text',
    'backend'            => '',
    'frontend'          => '',
    'source'            => '',
    'label'             => 'Item Code',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'required'          => '0',
    'user_defined'      => '1',
    'searchable'        => '0',
    'filterable'        => '0',
    'comparable'        => '0',
    'apply_to' => implode(',',
        array(
            Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
        )
    ),
    'visible_on_front'  => '1',
    'visible_in_advanced_search' => '0',
    'unique'            => '0'
));

$installer->addAttribute('catalog_product', 'previous_categories_ids', array(
    'type'              => 'varchar',
    'input'             => 'text',
    'backend'           => '',
    'frontend'          => '',
    'source'            => '',
    'label'             => 'Previous Categories (used to remove a product from sale)',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'required'          => '0',
    'user_defined'      => '0',
    'searchable'        => '0',
    'filterable'        => '0',
    'comparable'        => '0',
    'apply_to' => implode(',',
        array(
            Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
        )
    ),
    'visible_on_front'  => '0',
    'visible_in_advanced_search' => '0',
    'unique'            => '0'
));

$installer->endSetup();