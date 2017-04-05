<?php
$installer = $this;
$installer->startSetup();

$setup = Mage::getModel('sales/resource_Setup', 'core_setup');

$setup->addAttribute('order', 'rd_order_code', array('type' => 'varchar'));

$setup->endSetup();