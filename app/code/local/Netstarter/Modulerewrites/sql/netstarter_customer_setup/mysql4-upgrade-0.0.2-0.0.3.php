<?php
/**
 * @category    design
 * @package     enterprise_bnt
 * @copyright   www.netstarter.com.au
 * @license     www.netstarter.com.au
 */

/* @var $installer Netstarter_Checkout_Model_Resource_Setup */
$installer = $this;

//Set Customer_Address City Attribute is not a required field
$attribute = Mage::getModel('eav/entity_attribute')
    ->loadByCode('customer_address', 'city')
    ->setIsRequired(true)
    ->save();

$installer->endSetup();