<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gayan Thrimanne
 * Date: 2/14/13
 * Time: 2:42 PM
 * To change this template use File | Settings | File Templates.
 */
$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('netstarter_seo/seocms');

$installer->run("alter table ".$tableName." add `canonical_url` varchar(255);");

$installer->endSetup();
