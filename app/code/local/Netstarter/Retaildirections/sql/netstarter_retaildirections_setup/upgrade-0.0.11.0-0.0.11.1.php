<?php

$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('netstarter_retaildirections/synchronization');

$installer->getConnection()->dropTable($tableName);

/*
 * Resets DDL cache since we changed schema.
 */
$installer->getConnection()->resetDdlCache();
$installer->endSetup();