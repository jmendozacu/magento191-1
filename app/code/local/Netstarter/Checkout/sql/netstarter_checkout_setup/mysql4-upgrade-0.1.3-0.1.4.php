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

$attributeSetName= 'Bnt';
$attributeGroupName = 'BNT';
$attributeSetId = '';
$attributeGroupId = '';


$installer->removeAttribute('catalog_product', 'cart_comment');
$installer->removeAttribute('catalog_product', 'cart_comment_start_date');
$installer->removeAttribute('catalog_product', 'cart_comment_end_date');

$entityTypeId     = $installer->getEntityTypeId('catalog_product');
$attributeSet   = $installer->getAttributeSet($entityTypeId, $attributeSetName);

if ($attributeSet && isset($attributeSet['attribute_set_id'])) {

    $attributeSetId = $attributeSet['attribute_set_id'];
    $attributeGroup = $installer->getAttributeGroup($entityTypeId, $attributeSet['attribute_set_id'], $attributeGroupName);

    if ($attributeGroup && isset($attributeGroup['attribute_group_id'])) {
        $attributeGroupId = $attributeGroup['attribute_group_id'];
    }
}

if ($attributeSetId && $attributeGroupId) {

    // --- Attribute Cart Comment Stop Showing When
    $installer->addAttribute('catalog_product', 'cart_comment_end_date',  array(
        'type'              => 'datetime',
        'input'             => 'date',
        'label'             => 'Cart Comment Valid Until',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'backend'           => 'eav/entity_attribute_backend_datetime',
        'visible'           => true,
        'required'          => false,
        'user_defined'      => true,
        'default'           => '',
        'apply_to'          => 'configurable'
    ));

    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        'cart_comment_end_date',
        '11'                    //last Magento's attribute position in General tab is 10?
    );

    // --- Attribute Cart Comment Start Showing From
    $installer->addAttribute('catalog_product', 'cart_comment_start_date',  array(
        'type'              => 'datetime',
        'input'             => 'date',
        'label'             => 'Cart Comment Valid From',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'backend'           => 'eav/entity_attribute_backend_datetime',
        'visible'           => true,
        'required'          => false,
        'user_defined'      => true,
        'default'           => '',
        'apply_to'          => 'configurable'
    ));

    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        'cart_comment_start_date',
        '11'                    //last Magento's attribute position in General tab is 10?
    );


    // --- Attribute Cart Comment
    $installer->addAttribute('catalog_product', 'cart_comment',  array(
        'type'              => 'varchar',
        'input'             => 'text',
        'label'             => 'Cart Comment',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => true,
        'default'           => '',
        'apply_to'          => 'configurable'
    ));

    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        'cart_comment',
        '11'                    //last Magento's attribute position in General tab is 10?
    );

    //$attributeId = $installer->getAttributeId($entityTypeId, $attributeName);
    //$installer->removeAttribute('catalog_product', $attributeName);
}
$installer->endSetup();