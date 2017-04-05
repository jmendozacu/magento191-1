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
 * Product Compare List Model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Netstarter_Extcatalog_Model_Catalog_Product_Compare_List extends Mage_Catalog_Model_Product_Compare_List
{


    /**
     * Add visitor and customer data to compare item
     *
     * @param Mage_Catalog_Model_Product_Compare_Item $item
     * @return Mage_Catalog_Model_Product_Compare_List
     */
    protected function _addVisitorToItem($item)
    {
        $item->addVisitorId(Mage::getSingleton('log/visitor')->getId());
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $item->addCustomerData(Mage::getSingleton('customer/session')->getCustomer());
        }

        return $this;
    }

    /**
     * Update  product - Compare List
     *
     * @param int|Mage_Catalog_Model_Product $remove
     * @param int|Mage_Catalog_Model_Product $insert
     * @return Mage_Catalog_Model_Product_Compare_List
     */
    public function updateProduct($remove, $insert)
    {
        /* @var $item Mage_Catalog_Model_Product_Compare_Item */
        $item = Mage::getModel('catalog/product_compare_item');
        $this->_addVisitorToItem($item);
        $item->loadByProduct($remove);

        $updateitem = Mage::getModel('catalog/product_compare_item');
        $this->_addVisitorToItem($updateitem);
        $updateitem->loadByProduct($insert);

        if(!$updateitem->getCatalogCompareItemId()){

            if ($item->getCatalogCompareItemId()) {
                $item->setProductId($insert);
                $item->save();
            }
        }

        return $this;
    }

}
