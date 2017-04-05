<?php

$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('netstarter_shelltools/synchronization');

$table = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn('event_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Code of the synchronization event')
    ->addColumn('last_sync_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
    ), 'Date of the last Synchronization')
    ->addColumn('last_success_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
    ), 'Date of the last successful Synchronization')
    ->addColumn('last_sync_param_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
    ), 'Date passed as a param on the last Synchronization')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Status of the last synchronization event')
    ->addColumn('info', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => true,
    ), 'Extra info of the last synchronization event')

    ->addIndex($installer->getIdxName('netstarter_retaildirections/synchronization', array('event_code')),
        array('event_code'),
        array('type' => 'unique')
    )

    ->setComment('Synchronization Information');

$installer->getConnection()->dropTable($tableName);
$installer->getConnection()->createTable($table);

/*
 * Resets DDL cache since we changed schema.
 */
$installer->getConnection()->resetDdlCache();
$installer->endSetup();