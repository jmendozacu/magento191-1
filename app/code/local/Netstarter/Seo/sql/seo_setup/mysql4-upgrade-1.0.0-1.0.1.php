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

$option_freq = array(
    'value' => array(
        'never' => array(0 => 'Never'),
        'yearly' => array(0 => 'Yearly'),
        'monthly' => array(0 => 'Monthly'),
        'weekly' => array(0 => 'Weekly'),
        'daily' => array(0 => 'Daily'),
        'hourly' => array(0 => 'Hourly'),
        'always' => array(0 => 'Always'),
    ),
);

$option_prio = array(
    'value' => array(
        'A' => array(0 => '0'),
        'B' => array(0 => '1'),
        'C' => array(0 => '0.1'),
        'D' => array(0 => '0.2'),
        'E' => array(0 => '0.3'),
        'F' => array(0 => '0.4'),
        'G' => array(0 => '0.5'),
        'H' => array(0 => '0.6'),
        'I' => array(0 => '0.7'),
        'J' => array(0 => '0.8'),
        'K' => array(0 => '0.9'),
    ),
);



//Add Sitemap attributes to categories

$installer->addAttribute('catalog_category', 'cat_show_in_xml_sitemap', array(
    'group'         => 'Seo',
    'input'         => 'select',
    'type'          => 'varchar',
    'label'         => 'Show in XML Sitemap',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'sort_order'    => 6,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'option'        => $option_visibility,
));


$installer->addAttribute('catalog_category', 'cat_frequency', array(
    'group'         => 'Seo',
    'input'         => 'select',
    'type'          => 'varchar',
    'label'         => 'Frequency',
    'visible'       => 1,
    'required'      => 0,
    'user_defined' => 1,
    'sort_order'    => 7,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'option'        => $option_freq,
));

$installer->addAttribute('catalog_category', 'cat_priority', array(
    'group'         => 'Seo',
    'input'         => 'select',
    'type'          => 'varchar',
    'label'         => 'Priority',
    'visible'       => 1,
    'required'      => 0,
    'user_defined' => 1,
    'sort_order'    => 8,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'option'        => $option_prio,
));




//Add Sitemap attributes to products

$installer->addAttribute('catalog_product', 'prod_show_in_xml_sitemap', array(
    'group'         => 'Seo',
    'input'         => 'select',
    'type'          => 'varchar',
    'label'         => 'Show in XML Sitemap',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'sort_order'    => 6,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'option'        => $option_visibility,
));


$installer->addAttribute('catalog_product', 'prod_frequency', array(
    'group'         => 'Seo',
    'input'         => 'select',
    'type'          => 'varchar',
    'label'         => 'Frequency',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'sort_order'    => 7,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'option'        => $option_freq,
));

$installer->addAttribute('catalog_product', 'prod_priority', array(
    'group'         => 'Seo',
    'input'         => 'select',
    'type'          => 'varchar',
    'label'         => 'Priority',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'sort_order'    => 8,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'option'        => $option_prio,
));

$installer->endSetup();
