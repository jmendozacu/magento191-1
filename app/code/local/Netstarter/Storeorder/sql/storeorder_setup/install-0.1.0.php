<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'store_order_id', array(
        'TYPE'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'LENGTH'    => 20,
        'NULLABLE'  => true,
        'COMMENT'   => 'Order Store Id'
    ));
