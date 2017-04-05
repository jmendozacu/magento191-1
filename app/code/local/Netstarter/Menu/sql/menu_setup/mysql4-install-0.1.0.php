<?php
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute('catalog_category', 'navigation_image',  array(
    'group'    => 'General Information',
    'type'     => 'varchar',
    'label'    => 'Navigation Image',
    'input'    => 'image',
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'required' => 0,
    'default'  => 0,
    'visible'  => true,
    'user_defined'  => 1,
));

/*$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'navigation_image',
    '10'
);*/

$attributeId = $installer->getAttributeId($entityTypeId, 'navigation_image');

/*$installer->run("
INSERT INTO `{$installer->getTable('catalog_category_entity_int')}`
(`entity_type_id`, `attribute_id`, `entity_id`, `value`)
    SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, ''
        FROM `{$installer->getTable('catalog_category_entity')}`;
");*/

$installer->endSetup();
