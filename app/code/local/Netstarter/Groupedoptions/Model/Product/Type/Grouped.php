<?php
class Netstarter_Groupedoptions_Model_Product_Type_Grouped extends Mage_Catalog_Model_Product_Type_Grouped
{
    public function getAssociatedConfigurableProductHtml($view, $product)
    {
        return $view->getLayout()->createBlock('catalog/product_view_type_configurable')
        ->setTemplate('groupedconfigured/product/view/type/groupedconfigured/configurable.phtml')
        ->setProduct($product)
        ->toHtml();
    }

	/**
	 * Overriding this method to remove the 'required option' filter. We do this because required options are filtered
	 * and we want to be able to associate simple products with required options to grouped products.
	 */
	public function getAssociatedProducts($product = null)
	{
		if (!$this->getProduct($product)->hasData($this->_keyAssociatedProducts)) {
			$associatedProducts = array();

			if (!Mage::app()->getStore()->isAdmin()) {
				$this->setSaleableStatus($product);
			}

			$collection = $this->getAssociatedProductCollection($product)
			->addAttributeToSelect('*')
			->setPositionOrder()
			->addStoreFilter($this->getStoreFilter($product))
            ->joinAttribute('use_config_allow_message', 'catalog_product/use_config_allow_message', 'entity_id', null, 'left' )
			->addAttributeToFilter('status', array('in' => $this->getStatusFilters($product)));

//            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

			foreach ($collection as $item) {
				$associatedProducts[$item->getId()] = $item;
			}

			$this->getProduct($product)->setData($this->_keyAssociatedProducts, $associatedProducts);
		}
		return $this->getProduct($product)->getData($this->_keyAssociatedProducts);
	}

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and add logic specific to Grouped product type.
     *     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        $product = $this->getProduct($product);
        $productsInfo = $buyRequest->getSuperGroup();
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);

        if (!$isStrictProcessMode || (!empty($productsInfo) && is_array($productsInfo))) {
            $products = array();
            $associatedProductsInfo = array();
            $associatedProducts = $this->getAssociatedProducts($product);
            if ($associatedProducts || !$isStrictProcessMode) {
                foreach ($associatedProducts as $subProduct) {
                    $subProductId = $subProduct->getId();
                    if(isset($productsInfo[$subProductId])) {
                        $qty = $productsInfo[$subProductId];
                        if (!empty($qty) && is_numeric($qty)) {

                            if ($subProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                                $isConfigurable = true;

                                $configBuyRequest = clone $buyRequest;

                                $superAttr = $buyRequest->getSuperAttribute();
                                if (array_key_exists($subProductId, $superAttr)) {
                                    $configBuyRequest->setSuperAttribute($superAttr[$subProductId]);
                                }

                                $configBuyRequest->setQty($qty);

                                $_result = $subProduct->getTypeInstance(true)
                                    ->_prepareProduct($configBuyRequest, $subProduct, $processMode);
                            }elseif( $subProduct->getTypeId() == Enterprise_GiftCard_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD){

                                $isConfigurable = true;

                                $configBuyRequest = clone $buyRequest;

                                $configBuyRequest->setQty($qty);

                                $_result = $subProduct->getTypeInstance(true)
                                    ->_prepareProduct($configBuyRequest, $subProduct, $processMode);
                            }else {

                                $isConfigurable = false;

                                $clonedBuyRequest   = clone $buyRequest;
                                $superOptions       = $buyRequest->getSuperOptions();

                                if($superOptions && isset($superOptions[$subProduct->getId()])){
                                    $clonedBuyRequest->setOptions($superOptions[$subProduct->getId()]);
                                }

                                if ($subProduct->getHasOptions() && count($subProduct->getOptions()) == 0) {
                                    foreach ($subProduct->getProductOptionsCollection() as $_option) {
                                        $subProduct->addOption($_option);
                                    }
                                }
                                $_result = $subProduct->getTypeInstance(true)
                                    ->_prepareProduct($clonedBuyRequest, $subProduct, $processMode);
                            }

                            if (is_string($_result) && !is_array($_result)) {
                                return $_result;
                            }

                            if (!isset($_result[0])) {
                                return Mage::helper('checkout')->__('Cannot process the item.');
                            }


                            if ($isConfigurable) {
                                foreach ($_result as $item) {
                                    $products[] = $item;
                                }
                            } else {

                                if ($isStrictProcessMode) {
                                    $_result[0]->setCartQty($qty);
                                    $_result[0]->addCustomOption('product_type', self::TYPE_CODE, $product);

                                    $newBuyRequest = array(
                                        'super_product_config' => array(
                                            'product_type'  => self::TYPE_CODE,
                                            'product_id'    => $product->getId()
                                        )
                                    );

                                    if (isset($clonedBuyRequest)) {
                                        if ($clonedBuyRequest->getOptions()) {
                                            $newBuyRequest['options'] = $clonedBuyRequest->getOptions();
                                        }
                                        if ($clonedBuyRequest->getQty()) {
                                            $newBuyRequest['qty'] = $clonedBuyRequest->getQty();
                                        }
                                    }

                                    $_result[0]->addCustomOption('info_buyRequest', serialize($newBuyRequest));
                                    $products[] = $_result[0];
                                } else {
                                    $associatedProductsInfo[] = array($subProductId => $qty);
                                    $product->addCustomOption('associated_product_' . $subProductId, $qty);
                                }
                            }
                        }
                    }
                }
            }

            if (!$isStrictProcessMode || count($associatedProductsInfo)) {
                $product->addCustomOption('product_type', self::TYPE_CODE, $product);
                $product->addCustomOption('info_buyRequest',serialize($buyRequest));

                $products[] = $product;
            }

            if (count($products)) {
                return $products;
            }
        }

        return Mage::helper('catalog')->__('Please specify the quantity of product(s).');
    }
}