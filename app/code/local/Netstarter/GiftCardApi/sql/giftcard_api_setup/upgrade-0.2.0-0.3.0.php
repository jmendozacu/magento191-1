<?php
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('enterprise_giftcardaccount'), 'additional', 'varchar(100) NULL');
$installer->getConnection()->resetDdlCache();
$installer->endSetup();
