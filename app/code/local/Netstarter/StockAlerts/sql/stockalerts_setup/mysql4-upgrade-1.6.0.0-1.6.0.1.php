<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()->dropForeignKey($installer->getTable('productalert/stock'), 'FK_PRODUCT_ALERT_STOCK_CUSTOMER_ID_CUSTOMER_ENTITY_ENTITY_ID');

$installer->getConnection()->addColumn($installer->getTable('productalert/stock'),'customer_email', "varchar(60) NULL");
$installer->getConnection()->addColumn($installer->getTable('productalert/stock'),'guest_customer', "tinyint(2) NULL default 0");

$installer->endSetup();
