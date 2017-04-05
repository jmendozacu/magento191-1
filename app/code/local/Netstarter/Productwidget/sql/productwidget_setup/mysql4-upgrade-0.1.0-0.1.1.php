<?php
/* @var $installer  */

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('productwidget/look'), 'position', "smallint(5) NOT NULL DEFAULT 0");

$installer->endSetup();