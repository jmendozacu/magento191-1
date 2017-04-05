<?php
/**
 * To change this template use File | Settings | File Templates.
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$_mainTable = $installer->getTable('fitwizard/fitcategory');
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
    ->addColumn('cup', Varien_Db_Ddl_Table::TYPE_TINYINT, 3,
        array(
            'nullable' => false
        ), 'Cup Preference')
    ->addColumn('fullness', Varien_Db_Ddl_Table::TYPE_TINYINT, 3,
        array(
            'nullable' => false
        ), 'Fullness')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_TINYINT, 3,
        array(
            'nullable' => false
        ), 'Position')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
        array(
            'nullable' => false
        ),'Category Id')
    ->addIndex('answer_combination', array('cup', 'fullness', 'position'));
$installer->getConnection()->createTable($table);

$installer->endSetup();