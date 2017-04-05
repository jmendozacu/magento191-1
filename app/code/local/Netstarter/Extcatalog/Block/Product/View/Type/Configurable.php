<?php
class Netstarter_Extcatalog_Block_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable
{
	
	public $stock;

	public $proType;

    public $rand = 0;


    public function getPoductItems()
    {
        return $this->stock;
    }


    public function getAllowProducts()
    {
        if (!$this->hasAllowProducts()) {
            $products = array();

            $allProducts = $this->getProduct()->getTypeInstance(true)
                ->getUsedProducts(null, $this->getProduct());
            foreach ($allProducts as $product) {
                $products[] = $product;
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }

	public function getJsonConfig()
    {
        $this->rand = $this->getProduct()->getRandKey();
        if(!$this->rand)
            $this->rand = $this->getProduct()->getId().microtime();
		
        $attributes = array();
        $options    = array();
        $outStocks    = array();
        $stockStatus = array();
        $store      = $this->getCurrentStore();
        $taxHelper  = Mage::helper('tax');
        $currentProduct = $this->getProduct();

        $preconfiguredFlag = $currentProduct->hasPreconfiguredValues();
        if ($preconfiguredFlag) {
            $preconfiguredValues = $currentProduct->getPreconfiguredValues();
            $defaultValues       = array();
        }

        $skipSaleableCheck = Mage::helper('catalog/product')->getSkipSaleableCheck();

        foreach ($this->getAllowProducts() as $product) {

            $productId  = $product->getId();

            if ($product->isSaleable() || $skipSaleableCheck) {

                foreach ($this->getAllowAttributes() as $attribute) {
                    $productAttribute   = $attribute->getProductAttribute();
                    $productAttributeId = $productAttribute->getId();
                    $attributeValue     = $product->getData($productAttribute->getAttributeCode());
                    if (!isset($options[$productAttributeId])) {
                        $options[$productAttributeId] = array();
                    }

                    if (!isset($options[$productAttributeId][$attributeValue])) {
                        $options[$productAttributeId][$attributeValue] = array();
                    }
                    $options[$productAttributeId][$attributeValue][] = $productId;
                }
                $stockStatus[$product->getId()] = array('saleable' => 1, 'is_noncore'=> $product->getIsNoncore(), 'qty' => $product->getStockItem()->getQty());
            }else{

                /**
                 * Specific customization for size attribute
                 */
                $outStocks[$product->getSize()] = $productId;
                $stockStatus[$product->getId()] = array('saleable' => 0, 'is_noncore'=> $product->getIsNoncore(), 'qty' => 0);
            }
        }

        $this->_resPrices = array(
            $this->_preparePrice($currentProduct->getFinalPrice())
        );

        foreach ($this->getAllowAttributes() as $attribute) {
            $productAttribute = $attribute->getProductAttribute();

            $attributeIdRad = "{$this->rand}{$productAttribute->getId()}";
            $attributeId = $productAttribute->getId();

            $info = array(
               'id'        => $attributeIdRad,
               'code'      => $productAttribute->getAttributeCode(),
               'label'     => $attribute->getLabel(),
               'options'   => array()
            );

            $stockOptions = array();

            $optionPrices = array();
            $prices = $attribute->setPositionOrder('asc')->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $value) {

                    $productId = 0;

                    if(isset($options[$attributeId][$value['value_index']])) {

                        $productId = $options[$attributeId][$value['value_index']][0];
                    }elseif(isset($outStocks[$value['value_index']])){

                        $productId = $outStocks[$value['value_index']];
                    }

                    if(isset($stockStatus[$productId])){
                        $optionsArr = array( 'id' => $value['value_index'],
                                             'label' => $value['label']);

                        $stockOptions[$productId] = array_merge($stockStatus[$productId], $optionsArr);
                    }

                    if(!$this->_validateAttributeValue($attributeId, $value, $options)) {
                        continue;
                    }


                    $currentProduct->setConfigurablePrice(
                        $this->_preparePrice($value['pricing_value'], $value['is_percent'])
                    );
                    $currentProduct->setParentId(true);
                    Mage::dispatchEvent(
                        'catalog_product_type_configurable_price',
                        array('product' => $currentProduct)
                    );
                    $configurablePrice = $currentProduct->getConfigurablePrice();

                    if (isset($options[$attributeId][$value['value_index']])) {
                        $productsIndex = $options[$attributeId][$value['value_index']];
                    } else {
                        $productsIndex = array();
                    }

                    $info['options'][] = array(
                        'id'        => $value['value_index'],
                        'label'     => $value['label'],
                        'price'     => $configurablePrice,
                        'oldPrice'  => $this->_preparePrice($value['pricing_value'], $value['is_percent']),
                        'products'  => $productsIndex,
                        'qty'       => (isset($stockStatus[$productId]['qty']))?$stockStatus[$productId]['qty']:0
                    );
                    $optionPrices[] = $configurablePrice;
                    //$this->_registerAdditionalJsPrice($value['pricing_value'], $value['is_percent']);
                }
            }

            $this->stock[$this->rand.$attributeId] = $stockOptions;
            /**
             * Prepare formated values for options choose
             */
            foreach ($optionPrices as $optionPrice) {
                foreach ($optionPrices as $additional) {
                    $this->_preparePrice(abs($additional-$optionPrice));
                }
            }
            if($this->_validateAttributeInfo($info)) {
               $attributes[$attributeIdRad] = $info;
            }

            // Add attribute default value (if set)
            if ($preconfiguredFlag) {
                $configValue = $preconfiguredValues->getData('super_attribute/' . $attributeId);
                if ($configValue) {
                    $defaultValues[$attributeId] = $configValue;
                }
            }
        }

        $taxCalculation = Mage::getSingleton('tax/calculation');
        if (!$taxCalculation->getCustomer() && Mage::registry('current_customer')) {
            $taxCalculation->setCustomer(Mage::registry('current_customer'));
        }

        $_request = $taxCalculation->getRateRequest(false, false, false);
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $defaultTax = $taxCalculation->getRate($_request);

        $_request = $taxCalculation->getRateRequest();
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $currentTax = $taxCalculation->getRate($_request);

        $taxConfig = array(
            'includeTax'        => $taxHelper->priceIncludesTax(),
            'showIncludeTax'    => $taxHelper->displayPriceIncludingTax(),
            'showBothPrices'    => $taxHelper->displayBothPrices(),
            'defaultTax'        => $defaultTax,
            'currentTax'        => $currentTax,
            'inclTaxTitle'      => $this->__('Incl. Tax')
        );

        $config = array(
            'attributes'        => $attributes,
            'template'          => str_replace('%s', '#{price}', $store->getCurrentCurrency()->getOutputFormat()),
//            'prices'          => $this->_prices,
            'basePrice'         => $this->_registerJsPrice($this->_convertPrice($currentProduct->getFinalPrice())),
            'oldPrice'          => $this->_registerJsPrice($this->_convertPrice($currentProduct->getPrice())),
            'productId'         => $this->rand.$currentProduct->getId(),
            'chooseText'        => $this->__('Choose Size ...'),
            'taxConfig'         => $taxConfig
        );

        if ($preconfiguredFlag && !empty($defaultValues)) {
            $config['defaultValues'] = $defaultValues;
        }

        $config = array_merge($config, $this->_getAdditionalConfig());

        return Mage::helper('core')->jsonEncode($config);
    }
    
	public function getJsonConfigOptions()
    {
        $config = array();
        if (!$this->hasOptions()) {
            return Mage::helper('core')->jsonEncode($config);
        }

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_regularPrice = $this->getProduct()->getPrice();
        $_finalPrice = $this->getProduct()->getFinalPrice();
        $_priceInclTax = Mage::helper('tax')->getPrice($this->getProduct(), $_finalPrice, true);
        $_priceExclTax = Mage::helper('tax')->getPrice($this->getProduct(), $_finalPrice);

        $config = array(
            'productId'           => $this->rand.$this->getProduct()->getId(),
            'priceFormat'         => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax'          => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax'      => Mage::helper('tax')->displayPriceIncludingTax(),
            'showBothPrices'      => Mage::helper('tax')->displayBothPrices(),
            'productPrice'        => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice'     => Mage::helper('core')->currency($_regularPrice, false, false),
            'priceInclTax'        => Mage::helper('core')->currency($_priceInclTax, false, false),
            'priceExclTax'        => Mage::helper('core')->currency($_priceExclTax, false, false),
            /**
             * @var skipCalculate
             * @deprecated after 1.5.1.0
             */
            'skipCalculate'       => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax'          => $defaultTax,
            'currentTax'          => $currentTax,
            'idSuffix'            => '_clone',
            'oldPlusDisposition'  => 0,
            'plusDisposition'     => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition'    => 0,
            'tierPrices'          => 0,
            'plusDispositionTax'  => 0
        );

        $responseObject = new Varien_Object();
        Mage::dispatchEvent('catalog_product_view_config', array('response_object'=>$responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option=>$value) {
                $config[$option] = $value;
            }
        }

        return Mage::helper('core')->jsonEncode($config);
    }
}