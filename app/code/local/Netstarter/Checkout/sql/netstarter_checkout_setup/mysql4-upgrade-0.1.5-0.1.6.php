<?php
$installer = $this;
/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = new Mage_Sales_Model_Entity_Setup('core_setup');
$installer->getConnection()->addColumn($installer->getTable('sales/quote'), 'customer_newslettersubscribed', 'smallint(5) default 0');
//$installer->addAttribute('quote', 'customer_newslettersubscribed', array('type'=>'static'));
