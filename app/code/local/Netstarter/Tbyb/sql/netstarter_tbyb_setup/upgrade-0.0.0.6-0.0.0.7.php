<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('netstarter_tbyb/item'),
    'cancelled_at',
    Varien_Db_Ddl_Table::TYPE_DATETIME,
    null,
    array(
        'nullable'  => false,
        'default'   => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
    ), 'Date the item was cancelled');

$installer->getConnection()->resetDdlCache();
$installer->endSetup();

