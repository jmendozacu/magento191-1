<?php
/**
 * Class install
 *
 * @author bzhang@netstarter.com.au
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'bz_navigation/filter'
 */
//drop table when reinstall
$installer->getConnection()->dropTable($installer->getTable('bz_navigation/filter'));
$table = $installer->getConnection()
    ->newTable($installer->getTable('bz_navigation/filter'))
    ->addColumn('filter_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true
        ), 'Filter Id')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Attribute Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false
        ), 'Store Id')
    ->addColumn('display_mode', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0
        ), 'Display Mode')
    ->addColumn('image_mode', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0
        ), 'Enabled')
    ->addColumn('image_width', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => 30
        ), 'Image Width')
    ->addColumn('image_height', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => 30
        ), 'Image Height')
    ->addColumn('option_limit', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true
        ), 'Option Limit')
    ->addColumn('is_follow', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0
        ), 'Is Follow')
    ->addColumn('tooltip', Varien_Db_Ddl_Table::TYPE_TEXT, '64K', array(
        ), 'Tooltip')
    ->addColumn('meta_description', Varien_Db_Ddl_Table::TYPE_TEXT, '64K', array(
        ), 'Meta Description Template')
    ->addIndex($installer->getIdxName('bz_navigation/filter', array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName('bz_navigation/filter', array('store_id')),
        array('store_id'))
    ->addIndex(
        $installer->getIdxName(
            'bz_navigation/filter',
            array('attribute_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('attribute_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey($installer->getFkName('bz_navigation/filter', 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('bz_navigation/filter', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('BZ Navigation Filter');
$installer->getConnection()->createTable($table);

/**
 * Create table 'bz_navigation/filter_option'
 */
//drop table in case reinstall
$installer->getConnection()->dropTable($installer->getTable('bz_navigation/filter_option'));
$table = $installer->getConnection()
    ->newTable($installer->getTable('bz_navigation/filter_option'))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Value Id')
    ->addColumn('option_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Option Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Store Id')
    ->addColumn('block_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        'default'   => null
        ), 'Static Block Id')
    ->addColumn('color_code', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
        'nullable'  => true,
        'default'   => null
        ), 'Color Code')
    ->addColumn('filename', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null
        ), 'File Name')
    ->addColumn('filename_one', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null
        ), 'File Name One')
    ->addColumn('filename_two', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null
        ), 'File Name Two')
    ->addColumn('additional', Varien_Db_Ddl_Table::TYPE_TEXT, 2000, array(
        'nullable'  => true,
        'default'   => null
        ), 'Additional Text Field')
    ->addIndex($installer->getIdxName('bz_navigation/filter_option', array('option_id')),
        array('option_id'))
    ->addIndex($installer->getIdxName('bz_navigation/filter_option', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('bz_navigation/filter_option', array('block_id')),
        array('block_id'))
    ->addIndex(
        $installer->getIdxName(
            'bz_navigation/filter_option',
            array('option_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('option_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey(
        $installer->getFkName('bz_navigation/filter_option', 'option_id', 'eav/attribute_option', 'option_id'),
        'option_id', $installer->getTable('eav/attribute_option'), 'option_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('bz_navigation/filter_option', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('bz_navigation/filter_option', 'block_id', 'cms/block', 'block_id'),
        'block_id', $installer->getTable('cms/block'), 'block_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('BZ Navigation Filter Options');
$installer->getConnection()->createTable($table);