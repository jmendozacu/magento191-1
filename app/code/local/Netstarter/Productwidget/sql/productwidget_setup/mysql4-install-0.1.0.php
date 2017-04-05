<?php

$installer = $this;
$installer->startSetup();


$installer->run("

DROP TABLE IF EXISTS {$installer->getTable('productwidget/look')};
SET foreign_key_checks = 0;
CREATE TABLE `{$installer->getTable('productwidget/look')}` (
  `link_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Link ID',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Product ID',
  `linked_product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Linked Product ID',
  PRIMARY KEY (`link_id`),
  CONSTRAINT `FK_LOOK_PRO_ID` FOREIGN KEY (`linked_product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_LOOK_ENTITY_ID` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;"

);


$installer->endSetup();