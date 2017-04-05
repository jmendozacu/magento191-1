<?php

$installer = Mage::getResourceModel('catalog/setup','core_setup');
$installer->startSetup();

$installer->addAttribute('catalog_product', 'is_noncore', array(
    'type'              => 'varchar',
    'input'             => 'text',
    'backend'           => '',
    'frontend'          => '',
    'source'            => '',
    'label'             => 'Is Non-Core',
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