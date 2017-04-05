<?php
/**
 * Created by PhpStorm.
 * User: mrahman
 * Date: 17/04/15
 * Time: 4:07 PM
 */

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('newsletter/subscriber'),'bra_size', "varchar(10) NULL");
$installer->getConnection()->addColumn($installer->getTable('newsletter/subscriber'),'push-up', "tinyint(2) NULL");
$installer->getConnection()->addColumn($installer->getTable('newsletter/subscriber'),'Contour', "tinyint(2) NULL");
$installer->getConnection()->addColumn($installer->getTable('newsletter/subscriber'),'no_padding', "tinyint(2) NULL");
$installer->getConnection()->addColumn($installer->getTable('newsletter/subscriber'),'fullness', "tinyint(2) NULL");
$installer->getConnection()->addColumn($installer->getTable('newsletter/subscriber'),'position', "tinyint(2) NULL");


$installer->endSetup();