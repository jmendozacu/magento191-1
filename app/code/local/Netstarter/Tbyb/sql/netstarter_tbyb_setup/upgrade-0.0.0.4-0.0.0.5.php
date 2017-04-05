<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
        $installer->getTable('netstarter_tbyb/item'),
        'transaction_id',
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable'  => true,
            'length'    => '50',
            'comment'   => 'Transaction Id'
        ));

$installer->getConnection()->addColumn(
        $installer->getTable('netstarter_tbyb/item'),
        'response_message',
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable'  => true,
            'length'    => '1000',
            'comment'   => 'Response Message'
        ));

$installer->getConnection()->resetDdlCache();
$installer->endSetup();

