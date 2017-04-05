<?php

$installer = $this;

$installer->startSetup();

$installer->run("
--  DROP TABLE IF EXISTS {$this->getTable('location/main')};
CREATE TABLE `{$this->getTable('location/main')}` (
  `location_id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `store_location` point NOT NULL ,
  `email` varchar(64) NOT NULL DEFAULT '',
  `phone` varchar(64) NOT NULL DEFAULT '',
  `fax` varchar(64) NOT NULL DEFAULT '',
  `flag` tinyint unsigned NOT NULL DEFAULT 0,
  `active` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`location_id`),
  SPATIAL KEY (`store_location`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `{$this->getTable('location/info')}` (
  `info_id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `location_id` SMALLINT UNSIGNED,
  `hours` text,
  `address` text,
  `description` text,
  PRIMARY KEY (`info_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
");
$installer->endSetup();
?>
