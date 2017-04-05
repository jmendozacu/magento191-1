<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('newsletter/subscriber'),'subscription_date', "timestamp NULL");

$installer->endSetup();
