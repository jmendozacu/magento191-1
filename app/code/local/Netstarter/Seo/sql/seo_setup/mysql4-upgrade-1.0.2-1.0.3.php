<?php
$installer = $this;
$installer->startSetup();

// Add Category attribute - canonical URL
$installer->addAttribute('catalog_category', 'canonical_tag', array(
    'group'         => 'Seo',
    'input'         => 'text',
    'type'          => 'varchar',
    'label'         => 'Canonical URL',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'sort_order'    => 9,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
));

// Add Product attribute - canonical URL
$installer->addAttribute('catalog_product', 'canonical_tag', array(
    'group'         => 'Seo',
    'input'         => 'text',
    'type'          => 'varchar',
    'label'         => 'Canonical URL',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'sort_order'    => 9,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
));

$installer->endSetup();