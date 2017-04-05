<?php
$installer = $this;

$installer->startSetup();

$_infoTable = $installer->getTable('location/info');

$installer->getConnection()->addColumn($_infoTable, 'image_path', "varchar(100) NOT NULL DEFAULT ''");

$installer->run("

CREATE TABLE `{$this->getTable('location/spatial_index')}` (
  `location_id` SMALLINT UNSIGNED NOT NULL,
  `store_location` point NOT NULL ,
  PRIMARY KEY (`location_id`),
  SPATIAL KEY (`store_location`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

$installer->endSetup();
