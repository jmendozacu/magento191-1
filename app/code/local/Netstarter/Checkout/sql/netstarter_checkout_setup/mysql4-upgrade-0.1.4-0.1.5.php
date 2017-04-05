<?php
/**
 * @category    design
 * @package     enterprise_bnt
 * @copyright   www.netstarter.com.au
 * @license     www.netstarter.com.au
 */

/* @var $installer Netstarter_Checkout_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();



// --- Attribute Cart Comment Stop Showing When
$installer->removeAttribute('customer_address', 'suburb_postcode');

$setup = Mage::getModel('customer/entity_setup', 'core_setup');

$setup->addAttribute('customer_address', 'suburb_postcode',  array(
    'type'              => 'varchar',
    'input'             => 'text',
    'label'             => 'Postcode or Suburb',
    'global'            => 1,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => true,
    'default'           => '',
));

$installer->getConnection()->resetDdlCache();


$subAttribute = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'suburb_postcode');
$subAttribute->setData('used_in_forms', array(
    'adminhtml_customer_address',
    'customer_address_edit',
    'customer_register_address'
))->save();

$customer = Mage::getModel('customer/address');
$attrSetId = $customer->getResource()->getEntityType()->getDefaultAttributeSetId();
$setup->addAttributeToSet('customer_address', $attrSetId, 'General', 'suburb_postcode');

$installer->run("
    ALTER TABLE {$this->getTable('enterprise_customer_sales_flat_quote_address')} ADD COLUMN `suburb_postcode` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL ;
     ALTER TABLE {$this->getTable('enterprise_customer_sales_flat_order_address')} ADD COLUMN `suburb_postcode` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL ;
    ");

$installer->endSetup();

