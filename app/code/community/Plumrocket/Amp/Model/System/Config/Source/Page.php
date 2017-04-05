<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Amp
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Amp_Model_System_Config_Source_Page
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = array();
        foreach ($this->toArray() as $value => $label) {
            $result[] = array(
                'value' => $value,
                'label' => $label,
            );
        }
        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'cms_index_index' => Mage::helper('pramp')->__('Home Page'),
            'catalog_product_view' => Mage::helper('pramp')->__('Product Pages'),
            'catalog_category_view' => Mage::helper('pramp')->__('Category Pages'),
            'catalogsearch_result_index' => Mage::helper('pramp')->__('Catalog Search'),
            'cms_page_view' => Mage::helper('pramp')->__('CMS Pages'),
        );
    }

}
