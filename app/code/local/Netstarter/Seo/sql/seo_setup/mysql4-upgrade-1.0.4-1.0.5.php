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


// Drop down values for sitemap attributes

$option_visibility = array(
    'value' => array(
        'N' => array(0 => 'Hide'),
        'Y' => array(0 => 'Show'),
    ),
);

//Add Sitemap attributes to categories

$installer->addAttribute('catalog_category', 'cat_show_in_html_sitemap', array(
    'group'         => 'Seo',
    'input'         => 'select',
    'type'          => 'varchar',
    'label'         => 'Show in HTML Sitemap',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'sort_order'    => 5,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'option'        => $option_visibility,
));

//Add Sitemap attributes to products
/*
$installer->addAttribute('catalog_product', 'prod_show_in_html_sitemap', array(
    'group'         => 'Seo',
    'input'         => 'select',
    'type'          => 'varchar',
    'label'         => 'Show in HTML Sitemap',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'sort_order'    => 5,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'option'        => $option_visibility,
));
*/
$installer->endSetup();
