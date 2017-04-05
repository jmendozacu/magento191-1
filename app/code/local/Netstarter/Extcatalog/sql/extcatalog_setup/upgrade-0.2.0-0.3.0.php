<?php

$installer = Mage::getResourceModel('catalog/setup','core_setup');
$installer->startSetup();

$attributeSetName= 'Bnt';
$attributeGroupName = 'BNT';
$attributeSetId = '';
$attributeGroupId = '';

$entityTypeId     = $installer->getEntityTypeId('catalog_product');
$attributeSet   = $installer->getAttributeSet($entityTypeId, $attributeSetName);

if ($attributeSet && isset($attributeSet['attribute_set_id'])) {

    $attributeSetId = $attributeSet['attribute_set_id'];
    $attributeGroup = $installer->getAttributeGroup($entityTypeId, $attributeSet['attribute_set_id'], $attributeGroupName);

    if ($attributeGroup && isset($attributeGroup['attribute_group_id'])) {
        $attributeGroupId = $attributeGroup['attribute_group_id'];
    }
}

// features
$installer->removeAttribute('catalog_product', 'features');


$featureOption = array(
    'value' => array(
        'best_seller' => array(0 => 'Best Sellers'),
        'new' => array(0 => 'New'),
        'staff_pick' => array(0 => 'Staff Picks')
    ),
);

$installer->addAttribute('catalog_product', 'features', array(
    'type'              => 'varchar',
    'input'             => 'multiselect',
    'backend'            => 'eav/entity_attribute_backend_array',
    'frontend'          => '',
    'source'            => '',
    'label'             => 'Features',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'required'          => '0',
    'user_defined'      => '1',
    'searchable'        => '0',
    'filterable'        => '1',
    'comparable'        => '0',
    'option'            => $featureOption,
    'visible_on_front'  => '1',
    'unique'            => '0',
    'used_in_product_listing' => 1
));

if($entityTypeId && $attributeSetId && $attributeGroupId){

    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        'brands'
    );

    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        'features'
    );

    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        'youtube_video'
    );

    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        'more_colors'
    );
}


$installer->endSetup();