<?php
$installer = $this;

$installer->startSetup();

$_infoTable = $installer->getTable('location/web_store');

$table = $installer->getConnection()
    ->newTable($_infoTable)
    ->addColumn('web_store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
    array(
        'unsigned' => true,
        'identity' => true,
        'nullable' => false,
        'auto_increment' => true,
        'primary' => true
    ), 'Web_Store ID')
    ->addColumn('location_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
    array(
        'unsigned' => true,
        'nullable' => false,
    ), 'Store ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
    array(
        'unsigned' => true,
        'nullable' => false,
    ), 'web_store')
    ->addForeignKey('FK_LOCATION_WEBSITE_STORE', 'location_id',
    $installer->getTable('location/main'), 'location_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey('FK_STORE_WEBSITE_STORE', 'store_id',
    $installer->getTable('core/store'), 'store_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('StoreLocator Store Table');

$installer->getConnection()->createTable($table);

$installer->getConnection()->addForeignKey('FK_LOCATION_INFO_STORE',$installer->getTable('location/info'),'store_id',$installer->getTable('location/main'), 'location_id');

$installer->getConnection()->dropIndex($installer->getTable('location/main'), 'IDX_LOCATION_IDENTIFIER');
$installer->getConnection()->addIndex($installer->getTable('location/main'), 'IDX_LOCATION_IDENTIFIER', 'identifier', Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX);
$installer->getConnection()->resetDdlCache();
$installer->endSetup();
