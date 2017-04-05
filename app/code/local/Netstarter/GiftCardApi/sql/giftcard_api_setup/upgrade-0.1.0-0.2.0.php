<?php
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('enterprise_giftcardaccount'), 'type', 'varchar(60) NULL');
$installer->getConnection()->addColumn($this->getTable('enterprise_giftcardaccount'), 'pin_code', 'varchar(60) NULL');
$installer->getConnection()->resetDdlCache();
$installer->endSetup();
