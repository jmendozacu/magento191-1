<?php

$installer = Mage::getResourceModel('catalog/setup','core_setup');
$installer->startSetup();

$installer->removeAttribute('catalog_product', 'brands');

// brands

$brandOption = array(
    'value' => array(
        'playboy' => array(0 => 'Playboy'),
        'pop_colour' => array(0 => 'Pop Colour')
    ),
);

$installer->addAttribute('catalog_product', 'brands', array(
    'type'              => 'int',
    'input'             => 'select',
    'backend'            => '',
    'frontend'          => '',
    'source'            => 'eav/entity_attribute_source_table',
    'label'             => 'Brands',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'required'          => '0',
    'user_defined'      => '1',
    'searchable'        => '0',
    'filterable'        => '1',
    'comparable'        => '0',
    'option'            => $brandOption,
    'visible_on_front'  => '1',
    'unique'            => '0',
    'used_in_product_listing' => 1
));




// features
$installer->removeAttribute('catalog_product', 'features');


$featureOption = array(
    'value' => array(
        'best_seller' => array(0 => 'Best Sellers'),
        'new' => array(0 => 'New'),
        'staff_pick' => array(0 => 'Staff Picks')
    ),
);

$installer->addAttribute('catalog_product', 'features', array(
    'type'              => 'varchar',
    'input'             => 'multiselect',
    'backend'            => '',
    'frontend'          => '',
    'source'            => 'eav/entity_attribute_source_table',
    'label'             => 'Features',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'required'          => '0',
    'user_defined'      => '1',
    'searchable'        => '0',
    'filterable'        => '1',
    'comparable'        => '0',
    'option'            => $featureOption,
    'visible_on_front'  => '1',
    'unique'            => '0',
    'used_in_product_listing' => 1
));


// you tube video

$installer->removeAttribute('catalog_product', 'youtube_video');

$installer->addAttribute('catalog_product', 'youtube_video', array(
    'input'         => 'text',
    'type'          => 'varchar',
    'label'         => 'Youtube Video Code',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'sort_order'    => 9,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'used_in_product_listing' => 0
));

// more-colors

$installer->removeAttribute('catalog_product', 'more_colors');

$installer->addAttribute('catalog_product', 'more_colors',  array(
    'type'     => 'int',
    'label'    => 'More Colors',
    'input'    => 'boolean',
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => true,
    'default'           => 0,
    'source' => 'eav/entity_attribute_source_boolean',
    'used_in_product_listing' => 1
));

$installer->endSetup();