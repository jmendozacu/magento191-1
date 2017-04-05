<?php
$installer = Mage::getResourceModel('catalog/setup','core_setup');
$installer->startSetup();

$installer->updateAttribute('catalog_product', 'item_code', array('used_in_product_listing' => 1));
$installer->updateAttribute('catalog_product', 'color', array('is_configurable' => 0));
$installer->updateAttribute('catalog_product', 'simple_color', array('is_configurable' => 0));

$installer->endSetup();