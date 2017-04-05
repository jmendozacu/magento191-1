<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('fitwizard/fitcategory'),'status', "varchar(20) NULL");
$installer->getConnection()->addColumn($installer->getTable('fitwizard/fitcategory'),'backup_date', "timestamp NULL");

$installer->endSetup();
