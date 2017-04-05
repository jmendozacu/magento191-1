<?php

$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('netstarter_eway/token');

$table = $installer->getConnection()
    ->newTable($tableName)

    ->addColumn('token_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'  => true,
        'auto_increment' => true
    ), 'Token Id')

    ->addColumn('customer_id_eway', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Customer Id on Eway')

    ->addColumn('customer_id_magento', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Customer Id in Magento')
        
    ->addColumn('customer_email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Customer email')

    ->addColumn('token', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Eway Token')
        
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Magento website id')
        
    ->addColumn('currency_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Currency code used in the eWay token creation')
        
    ->addColumn('access_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Access code')
        
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        'default'	=> Varien_Db_Ddl_Table::TIMESTAMP_INIT,
    ), 'Date Row Was Created')

    ->addIndex($installer->getIdxName('netstarter_eway/token', array('token_id')),
        array('token_id')
    )

    ->addIndex($installer->getIdxName('netstarter_eway/token', array('customer_id_magento')),
        array('customer_id_magento')
    )

    ->addIndex($installer->getIdxName('netstarter_eway/token', array('customer_id_eway')),
        array('customer_id_eway')
    )
        
    ->addIndex($installer->getIdxName('netstarter_eway/token', array('customer_id_eway', 'website_id')),
        array('customer_id_eway', 'website_id')
    )

    ->addIndex($installer->getIdxName('netstarter_eway/token', array('customer_id_magento', 'website_id')),
        array('customer_id_magento', 'website_id'),
        array('type' => 'unique')
    )

    ->addIndex($installer->getIdxName('netstarter_eway/token', array('customer_email', 'website_id')),
        array('customer_email', 'website_id')
    )
        
    ->addForeignKey(
        $installer->getFkName('netstarter_eway/token', 'website_id', 'core/website', 'website_id'),
        'website_id',
        $installer->getTable('core/website'),
        'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )

    ->setComment('Eway token');

$installer->getConnection()->dropTable($tableName);
$installer->getConnection()->createTable($table);

/*
 * Resets DDL cache since we changed schema.
 */
$installer->getConnection()->resetDdlCache();
$installer->endSetup();