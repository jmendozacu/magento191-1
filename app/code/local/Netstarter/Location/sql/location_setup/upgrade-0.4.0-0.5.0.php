<?php
$installer = $this;

$installer->startSetup();


$installer->getConnection()->changeColumn($installer->getTable('location/main'), 'active', 'active', 'tinyint(1) unsigned NOT NULL DEFAULT 0');
$installer->getConnection()->changeColumn($installer->getTable('location/main'), 'flag', 'flag', 'tinyint(1) unsigned NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($installer->getTable('location/spatial_index'), 'active', "tinyint(1) unsigned NOT NULL DEFAULT 0");
$installer->getConnection()->addColumn($installer->getTable('location/main'), 'identifier', "varchar(40) NOT NULL DEFAULT ''");

$installer->endSetup();
