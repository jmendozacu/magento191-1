<?php
$installer = Mage::getResourceModel('catalog/setup','core_setup');
$installer->startSetup();
$installer->addAttribute('catalog_category', 'custom_link_url', array(
    'group'         => 'General Information',
    'input'         => 'text',
    'type'          => 'varchar',
    'default'       => '',
    'label'         => 'Category Redirect Url',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'user_defined'  => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'note'          => 'Hyperlink to a custom page.'
));

$installer->endSetup();

