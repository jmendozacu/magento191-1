<?php
/**
 * @author ben_zhanghf@hotmail.com
 */
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

if (version_compare(Mage::getVersion(), '1.8.1.0', '<=')){
    //Magento addColumn already check exist or not before adding :)
    $installer->getConnection()
    ->addColumn($installer->getTable('catalog/eav_attribute'), 'search_weight', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        'comment'   => 'Search Weight',
    ));
}

/*$installer->getConnection()->addIndex($installer->getTable('catalogsearch/search_query'),
    $installer->getIdxName('catalogsearch/search_query', array('num_results')),
    'num_results');
$installer->getConnection()->addIndex($installer->getTable('catalogsearch/search_query'),
    $installer->getIdxName('catalogsearch/search_query', array('query_text')),
    'query_text');
$installer->getConnection()->addIndex($installer->getTable('catalogsearch/search_query'),
    $installer->getIdxName('catalogsearch/search_query', array('query_text', 'store_id', 'num_results')),
    array('query_text', 'store_id', 'num_results'));*/

//Magento addColumn already check exist or not before adding :)
$installer->getConnection()
    ->addColumn($installer->getTable('cms/page'), 'is_searchable', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        'comment'   => 'Is Searchable'
    ));

$installer->getConnection()->addIndex($installer->getTable('cms/page'),
    $installer->getIdxName('cms/page', array('is_searchable')),
    'is_searchable');

$installer->endSetup();