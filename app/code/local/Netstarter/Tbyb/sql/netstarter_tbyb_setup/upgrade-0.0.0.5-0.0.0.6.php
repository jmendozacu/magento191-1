<?php

$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('sales/order');

$installer->getConnection()->changeColumn($tableName, "future_payment_date", "future_payment_date",
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'nullable'  => true,
        'unsigned'  => true,
        'comment'   => 'Future Payment Date'
    )
);

$installer->getConnection()->resetDdlCache();
$installer->endSetup();
