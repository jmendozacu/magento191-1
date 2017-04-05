<?php
$installer = Mage::getResourceModel('catalog/setup','core_setup');
$installer->startSetup();

$installer->updateAttribute('catalog_product', 'robot_tags', array('is_configurable' => 0));

$installer->endSetup();