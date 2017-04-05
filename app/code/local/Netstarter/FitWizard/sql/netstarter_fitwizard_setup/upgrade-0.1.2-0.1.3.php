<?php
/**
 * To change this template use File | Settings | File Templates.
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$_mainTable = $installer->getTable('fitwizard/net_fitwizard_coupons');
$installer->getConnection()->dropTable($_mainTable);

$table = $installer->getConnection()
    ->newTable($_mainTable)
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
        array(
            'unsigned' => true,
            'identity' => true,
            'nullable' => false,
            'auto_increment' => true,
            'primary' => true
        ), 'Entity Id')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
        array(
            'nullable' => false
        ), 'Email')
    ->addColumn('coupon_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50,
        array(
            'nullable' => false
        ), 'Coupon Code')
    ->addColumn('created_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, '',
        array(
            'nullable' => false
        ), 'Created Date')
    ->addIndex('email', array('email'));
$installer->getConnection()->createTable($table);

$installer->endSetup();