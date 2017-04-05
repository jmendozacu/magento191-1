<?php
$installer = Mage::getResourceModel('catalog/setup','core_setup');
$installer->startSetup();

$installer->addAttribute('catalog_category', 'show_children', array(
    'group'         => 'General Information',
    'input'         => 'select',
    'type'          => 'int',
    'default'       => 0,
    'label'         => 'Show child categories',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'source'        => 'eav/entity_attribute_source_boolean',
    'user_defined'  => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'note'          => 'Instead of showing sibling categories'
));

$installer->endSetup();