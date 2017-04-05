<?php
$installer = $this;

$installer->startSetup();

$_infoTable = $installer->getTable('location/main');

$installer->getConnection()->changeColumn($_infoTable, 'latitude', 'latitude', 'decimal(10,6) NOT NULL DEFAULT 0');
$installer->getConnection()->changeColumn($_infoTable, 'longitude', 'longitude', 'decimal(10,6) NOT NULL DEFAULT 0');

$installer->endSetup();
