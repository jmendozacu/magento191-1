<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gayan Thrimanne
 * Date: 2/14/13
 * Time: 2:42 PM
 * To change this template use File | Settings | File Templates.
 *
 * this will install a new table 'nets_seocms'
 * it will keep additional seo records of each cms page.
 */


$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('netstarter_seo/seocms');

$table = $installer->getConnection()
    ->newTable($tableName)

    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'  => true,
        'auto_increment' => true
    ), 'Seo CMS Id')

    ->addColumn('page_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'CMS Page Id')

    ->addColumn('pagetitle', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'CMS Meta Title')

    ->addColumn('show_in_xmlsitemap', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 1,
    ), 'Show in XML Sitemap')

    ->addColumn('frequency', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 'daily',
    ), 'Frequency')

    ->addColumn('priority', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0.3',
    ), 'Priority')

    ->addColumn('robot_tags', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default' => 'INDEX, FOLLOW',
    ), 'Robot Tags')

    ->addColumn('show_in_sitemap', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 1,
    ), 'Show in HTML Sitemap')

    ->addIndex($installer->getIdxName('netstarter_seo/seocms', array('page_id')), array('page_id'))

    ->addForeignKey($installer->getFkName('netstarter_seo/seocms', 'page_id', 'cms/page', 'page_id'),
        'page_id', $installer->getTable('cms/page'), 'page_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('CMS Page Information')
;
$installer->getConnection()->dropTable($tableName);
$installer->getConnection()->createTable($table);

$cmsPageTable = $installer->getTable('cms/page');

/*
Initially get existing cms page names as metatitles
default settings
show xml site map : 1 (yes)
show html site map : 1 (yes)
frequency : daily
priority : 0.3
*/
$installer->run("INSERT INTO ".$tableName." (`page_id`, `pagetitle`)
SELECT page_id, title FROM ".$cmsPageTable.";");

$installer->getConnection()->resetDdlCache();

$installer->endSetup();