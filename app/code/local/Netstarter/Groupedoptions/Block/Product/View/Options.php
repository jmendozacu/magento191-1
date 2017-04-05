<?php

class Netstarter_Groupedoptions_Block_Product_View_Options extends Mage_Core_Block_Template
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        if (!$this->hasData('template')) {
            $this->setTemplate('grouped-options/options.phtml');
        }
    }

    protected function _toHtml() {
        $layout = $this->getLayout();
        $product = $this->getProduct();

        if ($this->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
           
            if (Mage::helper('core')->isModuleEnabled('Mage_ConfigurableSwatches')
                && Mage::helper('configurableswatches')->isEnabled()) {
                $block = $layout->createBlock('catalog/product_view_type_configurable', 'go_configurable_block_' . $product->getId(), array(
                    'template'  => 'grouped-options/catalog/product/view/type/options/configurable.phtml',
                    'product'   => $this->getProduct()
                ));

                // Check for configurable product support
                $renderersBlock = $layout->createBlock('core/text_list', 'product.info.options.configurable.renderers');
                $block->setChild('attr_renderers', $renderersBlock);
                $swatchesBlock = $layout->createBlock(
                    'configurableswatches/catalog_product_view_type_configurable_swatches',
                    '',
                    array(
                        'template' => 'grouped-options/configurableswatches/catalog/product/view/type/options/configurable/swatches.phtml'
                    )
                );
                $renderersBlock->insert($swatchesBlock, '', false, 'swatches');


                $afterBlock = $layout->createBlock('core/text_list', 'product.info.options.configurable.after');
                $block->setChild('after', $afterBlock);
                $swatchesJsBlock = $layout->createBlock(
                    'configurableswatches/catalog_product_view_type_configurable_swatches',
                    '',
                    array(
                        'template' => 'grouped-options/configurableswatches/catalog/product/view/type/configurable/swatch-js.phtml'
                    )
                );
                $afterBlock->insert($swatchesJsBlock, '', false, 'swatch_js');

            } else {
                $block = $layout->createBlock('catalog/product_view_type_configurable', 'go_configurable_block_' . $product->getId(), array(
                    'template'  => $this->getConfigurableTemplate(),
                    'product'   => $this->getProduct()
                ));
            }
        } else {
            $block = $layout->createBlock('groupedoptions/product_view_type_simple', 'go_simple_block_'. $product->getId(), array(
                'template'  => $this->getSimpleTemplate(),
                'product'   => $this->getProduct()
            ));
        }
        
        $this->setRenderedProductHtml($block->toHtml());

        return parent::_toHtml();
    }
}