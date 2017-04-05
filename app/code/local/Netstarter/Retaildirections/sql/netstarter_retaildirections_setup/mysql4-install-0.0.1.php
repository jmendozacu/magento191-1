<?php

$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('netstarter_retaildirections/product');

$table = $installer->getConnection()
    ->newTable($tableName)

    ->addColumn('model_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'  => true,
        'auto_increment' => true
    ), 'Balance Id')

    ->addColumn('item_colour_ref', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('item_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('short_description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('extended_description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('lifecycle', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('item_family_group_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('width', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('depth', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('height', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('radius', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('brand_bame', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('division_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('base_colour_description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('base_colour_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('rrp', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('current_price', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('season_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('sell_code_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('size_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('quantity_available', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('division_description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('department_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('department_description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('category_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('category_description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('web_page_description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('colour_size_ind', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('item_family_group_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('item_family_group_desc', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('colour_description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('override_colour_desc', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('currency_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('sellable', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('web_display_ind', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('shape_type_ind', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('irregular_shape_ind', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('stock_pool_available', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('size_description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

    ->addColumn('go_with_item_list', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ))

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
    ->addColumn('retrieved_from_api_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
    ), 'Date Retrieved from the API')
    ->addColumn('translated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
    ), 'Date Translated')

    ->addIndex($installer->getIdxName('netstarter_retaildirections/product', array('sell_code_code')),
        array('sell_code_code')
    )

    ->addIndex($installer->getIdxName('netstarter_retaildirections/product', array('item_code')),
        array('item_code')
    )

    ->addIndex($installer->getIdxName('netstarter_retaildirections/product', array('item_colour_ref')),
        array('item_colour_ref')
    )

    ->addIndex($installer->getIdxName('netstarter_retaildirections/product', array('item_colour_ref', 'item_code', 'sell_code_code')),
        array('item_colour_ref', 'item_code', 'sell_code_code'),
        array('type' => 'unique')
    )

    ->addIndex($installer->getIdxName('netstarter_retaildirections/product', array('current_price')),
        array('current_price')
    )

    ->setComment('Product Model');

$installer->getConnection()->dropTable($tableName);
$installer->getConnection()->createTable($table);

/*
 * Resets DDL cache since we changed schema.
 */
$installer->getConnection()->resetDdlCache();
$installer->endSetup();