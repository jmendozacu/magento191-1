<?php
$installer = Mage::getResourceModel('catalog/setup','core_setup');
$installer->startSetup();

$installer->updateAttribute('catalog_product', 'color', array('is_configurable' => 0,
'is_filterable' => 0,
'is_visible_in_advanced_search' => 0,
'is_searchable'=>0,
'used_in_product_listing' => 1));

$installer->updateAttribute('catalog_product', 'simple_color', array('is_configurable' => 0,
    'is_filterable' => 0,
    'is_visible_in_advanced_search' => 0,
    'is_searchable'=>0,
    'used_in_product_listing' => 1));

$installer->endSetup();