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

$installer->addAttribute('customer_address', 'suburb_postcode',  array(
    'type'              => 'int',
    'input'             => 'text',
    'label'             => 'Postcode or Suburb',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => true,
    'default'           => '',
));

$installer->endSetup();