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
$installer->removeAttribute('catalog_product', 'group_gift_voucher');

$installer->addAttribute('catalog_product', 'group_gift_voucher', array(
    'type'     => 'int',
    'label'    => 'Is group gift voucher?',
    'input'    => 'boolean',
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => true,
    'default'           => 0,
    'source' => 'eav/entity_attribute_source_boolean',
    'used_in_product_listing' => 0,
    'apply_to' => 'grouped'
));

if($entityTypeId && $attributeSetId && $attributeGroupId){

    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        'group_gift_voucher'
    );
}

$installer->endSetup();