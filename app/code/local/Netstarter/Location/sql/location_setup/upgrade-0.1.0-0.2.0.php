<?php
$installer = $this;

$installer->startSetup();

$_mainTable = $installer->getTable('location/main');
$_infoTable = $installer->getTable('location/info');

$installer->getConnection()->dropTable($_mainTable);

$table = $installer->getConnection()
    ->newTable($_mainTable)
    ->addColumn('location_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
        array(
            'unsigned' => true,
            'identity' => true,
            'nullable' => false,
            'auto_increment' => true,
            'primary' => true
        ), 'StoreLocator ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 70,
        array(
            'nullable' => false
        ), 'Title')
    ->addColumn('latitude', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(10,8),
        array(
            'nullable' => false
        ), 'StoreLocator latitude')
    ->addColumn('longitude', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(10,8),
        array(
            'nullable' => false
        ), 'StoreLocator longitude')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100,
        array(
            'nullable' => false,
            'default'  => ''
        ), 'email')
    ->addColumn('phone', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30,
        array(
            'nullable' => false,
            'default'  => ''
        ), 'StoreLocator Phone')
    ->addColumn('fax', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30,
        array(
            'nullable' => false,
            'default'  => ''
        ), 'StoreLocator Fax')
    ->addColumn('flag', Varien_Db_Ddl_Table::TYPE_TINYINT, 255,
        array(
            'nullable' => false,
            'default'  => 0,
            'unsigned' => true,
        ), 'StoreLocator flag')
    ->addColumn('active', Varien_Db_Ddl_Table::TYPE_TINYINT, 255,
        array(
            'nullable' => false,
            'default'  => 0,
            'unsigned' => true,
        ), 'StoreLocator Store Website')
    ->setComment('StoreLocator Table');
$installer->getConnection()->createTable($table);


$installer->getConnection()->dropTable($_infoTable);

$table = $installer->getConnection()
    ->newTable($_infoTable)
    ->addColumn('info_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
        array(
            'unsigned' => true,
            'identity' => true,
            'nullable' => false,
            'auto_increment' => true,
            'primary' => true
        ), 'Info ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ), 'Store ID')
    ->addColumn('hours', Varien_Db_Ddl_Table::TYPE_TEXT, null,
        array(
            'nullable' => false,
            'default'  => ''
        ), 'StoreLocator Phone')
    ->addColumn('address', Varien_Db_Ddl_Table::TYPE_VARCHAR, null,
        array(
            'nullable' => false,
            'default'  => ''
        ), 'StoreLocator Phone')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_VARCHAR, null,
        array(
            'nullable' => false,
            'default'  => ''
        ), 'StoreLocator Phone')
    ->addForeignKey(
        $installer->getFkName('location/info', 'location_id',
            'location/main', 'location_id'), 'store_id',
        $installer->getTable('location/main'), 'location_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('StoreLocator info Table');

$installer->getConnection()->createTable($table);

$installer->endSetup();
