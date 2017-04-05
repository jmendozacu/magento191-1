<?php

$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('netstarter_eway/token');

$installer->getConnection()->dropColumn($tableName, "customer_id_eway");
$installer->getConnection()->changeColumn($tableName, "customer_id_magento", "customer_id",
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable'  => false,
        'unsigned'  => true,
        'comment'   => 'Customer Id in Magento'
    )
);

$installer->getConnection()->resetDdlCache();
$installer->endSetup();
