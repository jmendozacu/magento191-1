<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Catalog products compare block
 *
 * @category   Netstarter
 * @package    Netstarter_Modulerewrites
 * @author     httpd://www.netstarter.com.au
 * @desc       Extended from Mage_Catalog_Block_Product_Compare_List to add custom functioanality
 */
class Netstarter_Extcatalog_Block_Product_Compare_List extends Mage_Catalog_Block_Product_Compare_List
{
    const MAX_PRODUCT_LIMIT  = 50;

    /**
     * Retrieve Product Compare items collection
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Compare_Item_Collection
     */
    public function getItems()
    {
        if (is_null($this->_items)) {
            Mage::helper('catalog/product_compare')->setAllowUsedFlat(false);

            $this->_items = Mage::getResourceModel('catalog/product_compare_item_collection')
                ->useProductItem(true)
                ->setStoreId(Mage::app()->getStore()->getId());

            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->_items->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
            } elseif ($this->_customerId) {
                $this->_items->setCustomerId($this->_customerId);
            } else {
                $this->_items->setVisitorId(Mage::getSingleton('log/visitor')->getId());
            }

            $this->_items
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->loadComparableAttributes()
                //->addMinimalPrice()
                ->addTaxPercents();

            $this->_items->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                Mage::app()->getStore()->getId()
            );

            $this->_items->joinAttribute(
                'final_price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                Mage::app()->getStore()->getId()
            );

            Mage::getSingleton('catalog/product_visibility')
                ->addVisibleInSiteFilterToCollection($this->_items);

        }

        return $this->_items;
    }
}
