<?php
$installer = $this;

$installer->startSetup();

$installer->getConnection()->changeColumn($installer->getTable('location/info'), 'meta_title', 'meta_title', 'varchar(255) NOT NULL DEFAULT ""');
$installer->getConnection()->dropForeignKey($installer->getTable('location/info'), $installer->getFkName('location/info', 'location_id','location/main', 'location_id'));
$installer->getConnection()->addIndex($installer->getTable('location/main'), 'IDX_LOCATION_IDENTIFIER', 'identifier', Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);
$installer->endSetup();
