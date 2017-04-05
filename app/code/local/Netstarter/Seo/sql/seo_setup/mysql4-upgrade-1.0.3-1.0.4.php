<?php
$installer = $this;
$installer->startSetup();

$option_robot_tags = array(
    'value' => array(
        'A' => array(0 => 'NOINDEX, FOLLOW'),
        'B' => array(0 => 'INDEX, NOFOLLOW'),
        'C' => array(0 => 'NOINDEX, NOFOLLOW'),
        'D' => array(0 => 'INDEX, FOLLOW'),
    ),
);

$installer->addAttribute('catalog_category', 'robot_tags',  array(
    'group'         => 'Seo',
    'type'     		=> 'varchar',
    'label'    		=> 'Robot Tags',
    'input'    		=> 'select',
    'global'   		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'       => true,
    'required'      => false,
    'user_defined'  => false,
    'sort_order'    => 10,
    'option'        => $option_robot_tags,
));

$installer->addAttribute('catalog_product', 'robot_tags', array(
    'group'         => 'Seo',
    'input'         => 'select',
    'type'          => 'varchar',
    'label'         => 'Robot Tags',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'    => 10,
    'option'        => $option_robot_tags,
));

$installer->endSetup();