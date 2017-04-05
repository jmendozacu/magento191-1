<?php
/**
 * Catalog Product Compare Helper
 *
 * @category   Netstarter
 * @package    Netstarter_Modulerewrites
 * @author     http://www.netstarter.com.au
 * @license    http://www.netstarter.com.au/license.txt
 * @desc       Overwritten to add custom functionality from Mage_Catalog_Helper_Product_Compare
 */
class Netstarter_Modulerewrites_Helper_Catalog_Product_Compare extends Mage_Catalog_Helper_Product_Compare
{

    /**
     * Retrieve compare list items collection
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Compare_Item_Collection
     */
    public function getItemCollection()
    {
        if (!$this->_itemCollection) {
            /** @var Mage_Catalog_Model_Resource_Product_Compare_Item_Collection _itemCollection */
            $this->_itemCollection = Mage::getResourceModel('catalog/product_compare_item_collection')
                ->useProductItem(true)
                ->setStoreId(Mage::app()->getStore()->getId());

            if ($this->_customerSession->isLoggedIn()) {
                $this->_itemCollection->setCustomerId($this->_customerSession->getCustomerId());
            } elseif ($this->_customerId) {
                $this->_itemCollection->setCustomerId($this->_customerId);
            } else {
                $this->_itemCollection->setVisitorId($this->_logVisitor->getId());
            }

            $this->_productVisibility->addVisibleInSiteFilterToCollection($this->_itemCollection);

            //Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($this->_productCollection);

            /* Price data is added to consider item stock status using price index */
            //$this->_itemCollection->addPriceData();

            $this->_itemCollection->addAttributeToSelect('name')
                ->addAttributeToSelect('small_image')
                ->addUrlRewrite();

            $this->_itemCollection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                Mage::app()->getStore()->getId()
            );

            $this->_itemCollection->joinAttribute(
                'final_price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                Mage::app()->getStore()->getId()
            );

            $this->_itemCollection->load();

            /* update compare items count */
            $this->_catalogSession->setCatalogCompareItemsCount(count($this->_itemCollection));
        }

        return $this->_itemCollection;
    }
}
