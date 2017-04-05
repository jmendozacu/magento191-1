<?php

$installer = Mage::getResourceModel('catalog/setup','core_setup');
$installer->startSetup();

$netstarter_rdws_products_model = array(
    array('base_colour_name' => ''),
    array('base_colour_name' => 'Base'),
    array('base_colour_name' => 'Black'),
    array('base_colour_name' => 'Blue'),
    array('base_colour_name' => 'Brown'),
    array('base_colour_name' => 'Clear'),
    array('base_colour_name' => 'Dark Blue'),
    array('base_colour_name' => 'Dark Brown'),
    array('base_colour_name' => 'Dark Green'),
    array('base_colour_name' => 'Dark Grey'),
    array('base_colour_name' => 'Dark Pink'),
    array('base_colour_name' => 'Dark Purple'),
    array('base_colour_name' => 'Dark Red'),
    array('base_colour_name' => 'Gold'),
    array('base_colour_name' => 'Green'),
    array('base_colour_name' => 'Grey'),
    array('base_colour_name' => 'Ivory'),
    array('base_colour_name' => 'Light Blue'),
    array('base_colour_name' => 'Light Brown (Tan)'),
    array('base_colour_name' => 'Light Green'),
    array('base_colour_name' => 'Light Grey'),
    array('base_colour_name' => 'Light Orange'),
    array('base_colour_name' => 'Light Pink'),
    array('base_colour_name' => 'Light Purple'),
    array('base_colour_name' => 'Light Red'),
    array('base_colour_name' => 'Light Yellow'),
    array('base_colour_name' => 'Multi Colour'),
    array('base_colour_name' => 'Orange'),
    array('base_colour_name' => 'Pink'),
    array('base_colour_name' => 'Purple'),
    array('base_colour_name' => 'Red'),
    array('base_colour_name' => 'Silver'),
    array('base_colour_name' => 'Skin'),
    array('base_colour_name' => 'Stone'),
    array('base_colour_name' => 'White'),
    array('base_colour_name' => 'Yellow')
);

$colorOptionsArray = array();
for($i = 0; $i < count($netstarter_rdws_products_model); $i++)
{
    $colorOptionsArray['option_'.$i] = array($netstarter_rdws_products_model[$i]['base_colour_name'], $netstarter_rdws_products_model[$i]['base_colour_name']);
}

$installer->addAttribute('catalog_product', 'rd_category', array(
    'type'              => 'varchar',
    'input'             => 'text',
    'backend'           => '',
    'frontend'          => '',
    'source'            => '',
    'label'             => 'RD Category',
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

$installer->addAttribute('catalog_product', 'simple_color', array(
    'type'              => 'int',
    'input'             => 'select',
    'backend'            => '',
    'frontend'          => '',
    'source'            => 'eav/entity_attribute_source_table',
    'label'             => 'Simple Color',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'required'          => '0',
    'user_defined'      => '1',
    'searchable'        => '1',
    'filterable'        => '1',
    'comparable'        => '1',
    'option'            => array ('value' => $colorOptionsArray),
    'apply_to' => implode(',',
        array(
            Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
        )
    ),
    'visible_on_front'  => '1',
    'visible_in_advanced_search' => '1',
    'unique'            => '0'
));

$installer->endSetup();