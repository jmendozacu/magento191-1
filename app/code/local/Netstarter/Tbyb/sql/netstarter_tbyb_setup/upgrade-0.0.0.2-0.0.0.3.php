<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
        $installer->getTable('netstarter_tbyb/item'),
        'future_payment_date',
        Varien_Db_Ddl_Table::TYPE_DATETIME,
        null,
        array(
        'nullable'  => false,
        'default'   => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
    ), 'Future Payment Date');

$installer->getConnection()->resetDdlCache();
$installer->endSetup();

