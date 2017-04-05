<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
        $installer->getTable('netstarter_tbyb/item'),
        'qty',
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable'  => false,
            'default'   => '0.0000',
            'length'    => '12,4',
            'comment'   => 'Quantity Ordered'
        ));

$installer->getConnection()->resetDdlCache();
$installer->endSetup();

