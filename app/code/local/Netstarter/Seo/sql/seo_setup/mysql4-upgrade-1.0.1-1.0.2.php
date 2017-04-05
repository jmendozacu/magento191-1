<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gayan Thrimanne
 * Date: 2/14/13
 * Time: 2:42 PM
 * To change this template use File | Settings | File Templates.
 */


$installer = $this;
$installer->startSetup();




//Add category_h1_name attribute to categories

$installer->addAttribute('catalog_category', 'category_h1_name', array(
    'group'         => 'Seo',
    'input'         => 'text',
    'type'          => 'varchar',
    'label'         => 'H1 Tag - Product Category Name',
    'visible'       => 1,
    'required'      => 0,
    'user_defined' => 1,
    'sort_order'    => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'sort_order'    => 0,
));

//Add product_h1_name attribute to products

$installer->addAttribute('catalog_product', 'product_h1_name', array(
    'group'         => 'Seo',
    'input'         => 'text',
    'type'          => 'varchar',
    'label'         => 'H1 Tag - Product Name',
    'visible'       => 1,
    'required'      => 0,
    'user_defined' => 1,
    'sort_order'    => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'sort_order'    => 0,
));

$installer->endSetup();
