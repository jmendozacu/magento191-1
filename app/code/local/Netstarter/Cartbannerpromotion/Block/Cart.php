<?php
/**
 * Created by PhpStorm.
 * User: Tuan
 * Date: 3/25/14
 * Time: 1:00 PM
 */

class Netstarter_Cartbannerpromotion_Block_Cart extends Mage_Catalog_Block_Product_View {
    protected function _prepareLayout()
    {
        if (Mage::registry('product')){
            $block = $this->getLayout()->getBlock('catalog_product_price_template');
            if ($block) {
                foreach ($block->getPriceBlockTypes() as $type => $priceBlock) {
                    $this->addPriceBlockType($type, $priceBlock['block'], $priceBlock['template']);
                }
            }
        }
        return $this;
    }
    /**
     * Retrieve current product model
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!Mage::registry('product') && $this->getProductId()) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            Mage::register('product', $product);
        }
        return Mage::registry('product');
    }

    public function showPromotionBanner(){
        $sendValue = array();
        $currentData = date("Y-m-d", Mage::getModel('core/date')->timestamp(time()));





        $promotions = Mage::getModel('cartbannerpromotion/promotionlist')
            ->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('promotion_start',
                array(
                    array('to' => Mage::getModel('core/date')->gmtDate()),
                    array('promotion_start', 'null'=>''))
            )
            ->addfieldtofilter('promotion_end',
                array(
                    array('gteq' => Mage::getModel('core/date')->gmtDate()),
                    array('promotion_end', 'null'=>''))
            )
            ->getFirstItem()
        ;
        if($promotions->getData()){
            $cart = Mage::getModel('checkout/cart')->getQuote();
            if($cart){
                foreach ($cart->getAllItems() as $item) {
                    if($promotions->getProductId() ==$item->getProduct()->getId()){
                        return false;
                    }
                }
                $sendValue['PromotionId'] = $promotions->getPromotionId();
                $sendValue['ProductId'] = $promotions->getProductId();
                return $sendValue;
            }
        }
        return false;

    }
    public function getBanner($id){
        return Mage::getModel('cartbannerpromotion/promotionlist')->load($id);
    }
    public function getProductDetails($id){
        if(!Mage::registry('product')){
            $product = Mage::getModel('catalog/product')->load($id);
            Mage::register('product', $product);
            return $product;
        }else{
            return Mage::registry('product');
        }

    }
	 
    

} 