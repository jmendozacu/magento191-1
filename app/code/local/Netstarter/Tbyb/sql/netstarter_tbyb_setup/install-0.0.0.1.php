<?php

$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('netstarter_tbyb/item');

$table = $installer->getConnection()
    ->newTable($tableName)

    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'  => true,
        'auto_increment' => true
    ), 'Item Id')
        
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Increment Id')

    ->addColumn('order_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))
        
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))
        
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
    ), 'SKU / Sell code')
        
    ->addColumn('item_colour_ref', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
    ), 'Parent SKU / Item Colour Ref')

    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Customer Id')

    ->addColumn('customer_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Customer Name')
        
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Magento website id')
        
    ->addColumn('currency_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Currency code used in the eWay token creation')
        
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Price')

    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Status')
   
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        'default'	=> Varien_Db_Ddl_Table::TIMESTAMP_INIT,
    ), 'Date Row Was Created')
        
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,


// Line below commented out due to : "General error: 1293 Incorrect table definition; there can be only
// one TIMESTAMP column with CURRENT_TIMESTAMP in DEFAULT or ON UPDATE clause"
//
// - It is fixed on mysql 5.6:
// http://mysqlblog.fivefarmers.com/2012/05/29/overlooked-mysql-5-6-new-features-timestamp-and-datetime-improvements/
// but we are using a older version.
//
// Manually implementing "on update" date for retro-compatibility on resource


//		'default'	=> Varien_Db_Ddl_Table::TIMESTAMP_INIT_UPDATE,
    ), 'Date Row Was Updated')

    ->addIndex($installer->getIdxName('netstarter_tbyb/item', array('item_id')),
        array('item_id')
    )

    ->addIndex($installer->getIdxName('netstarter_tbyb/item', array('increment_id')),
        array('increment_id')
    )

    ->addIndex($installer->getIdxName('netstarter_tbyb/item', array('customer_id', 'website_id')),
        array('customer_id', 'website_id')
    )
        
    ->addForeignKey(
        $installer->getFkName('netstarter_tbyb/item', 'website_id', 'core/website', 'website_id'),
        'website_id',
        $installer->getTable('core/website'),
        'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
        
    ->setComment('Try before you buy management');

$installer->getConnection()->dropTable($tableName);
$installer->getConnection()->createTable($table);

/*
 * Resets DDL cache since we changed schema.
 */
$installer->getConnection()->resetDdlCache();
$installer->endSetup();