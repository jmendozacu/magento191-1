<?php
$installer = $this;
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('afeature/afeature')};
CREATE TABLE {$this->getTable('afeature/afeature')} (
  `afeature_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(25) NOT NULL default '',
  `is_hidden` TINYINT(1) unsigned NOT NULL default 0,
  `image_url` varchar(20) NOT NULL default '',
  `mobile_image_url` varchar(20) NOT NULL default '',
  `has_text` TINYINT(1) unsigned NOT NULL default 0,
  `text_position`TINYINT(1) unsigned NOT NULL default 0,
  `long_desc` varchar(40) NOT NULL default '',
  `short_desc` varchar(20) NOT NULL default '',
  `link_text` varchar(10) NOT NULL default '',
  `alt` varchar(20) NOT NULL default '',
  `active` TINYINT(1) unsigned NOT NULL default 0,
  `url` varchar(100) NOT NULL default '',
  PRIMARY KEY (`afeature_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->endSetup(); 