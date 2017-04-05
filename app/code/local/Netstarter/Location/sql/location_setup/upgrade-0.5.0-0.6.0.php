<?php
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('location/info'), 'meta_title', "TEXT NOT NULL DEFAULT ''");
$installer->getConnection()->addColumn($installer->getTable('location/info'), 'meta_description', " TEXT NOT NULL DEFAULT ''");
$installer->getConnection()->addColumn($installer->getTable('location/info'), 'meta_keywords', "TEXT NOT NULL DEFAULT ''");

$installer->endSetup();
