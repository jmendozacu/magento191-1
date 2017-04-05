<?php
// @var $installer Mage_Core_Model_Resource_Setup
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('cartbannerpromotion/promotionlist'))
    ->addColumn(
        'promotion_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ),
        'Promotion Id'
    )
    ->addColumn(
        'promotion_name', Varien_Db_Ddl_Table::TYPE_VARCHAR,
        65,
        array(
        'nullable'  => false,
        ),
        'Promotion Name'
    )
    ->addColumn(
        'product_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ),
        'Promotion Id'
    )
    ->addColumn(
        'product_name', Varien_Db_Ddl_Table::TYPE_VARCHAR,
        65,
        array(
            'nullable'  => false,
        ),
        'Product Name'
    )
    ->addColumn(
        'promotion_text', Varien_Db_Ddl_Table::TYPE_VARCHAR,
        125,
        array(
            'nullable'  => false,
        ),
        'Promotion Text'
    )
    ->addColumn(
        'banner', Varien_Db_Ddl_Table::TYPE_VARCHAR,
        125,
        array(
            'nullable'  => false,
        ),
        'banner'
    )
    ->addColumn(
        'promotion_start', Varien_Db_Ddl_Table::TYPE_DATETIME,
        null,
        array(
            'nullable'  => true,
        ),
        'Promotion Start'
    )
    ->addColumn(
        'promotion_end', Varien_Db_Ddl_Table::TYPE_DATETIME,
        null,
        array(
            'nullable'  => true,
        ),
        'Promotion End'
    )
    ->addColumn(
        'status', Varien_Db_Ddl_Table::TYPE_TINYINT,
        null,
        array(
            'nullable'  => false,
        ),
        'Status'
    );

$installer->getConnection()->createTable($table);