<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 9/9/13
 * Time: 8:01 AM
 * To change this template use File | Settings | File Templates.
 */

class Netstarter_Checkout_Model_Observer
{

    /**
     * add custom attributes which are not part of flat, to the product collection
     * on sales_quote_item_collection_products_after_load event
     *
     * @param $observer
     * @return $this
     */
    public function addModelDecoration($observer)
    {
        try{

            $productCollection = $observer->getEvent()->getProductCollection();

            $productIds = array();
            foreach ($productCollection as $product) {
                $productIds[] = $product->getId();
            }

            $collection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToFilter('entity_id',array('in' => $productIds))
                ->joinAttribute( 'cart_comment', 'catalog_product/cart_comment', 'entity_id', null, 'left' )
                ->joinAttribute( 'cart_comment_end_date', 'catalog_product/cart_comment_end_date', 'entity_id', null, 'left' )
                ->joinAttribute( 'cart_comment_start_date', 'catalog_product/cart_comment_start_date', 'entity_id', null, 'left' )
                ->joinAttribute( 'on_sale', 'catalog_product/on_sale', 'entity_id', null, 'left' )
                ->addAttributeToSelect(array('cart_comment','cart_comment_end_date','cart_comment_start_date'))
                ->addAttributeToSelect('color')
                ->addAttributeToSelect('on_sale');

            foreach ($collection as $attr) {

                $product = $productCollection->getItemById($attr->getEntityId());
                if ($product) {

                    $attrs = array('cart_comment'=> $attr->getCartComment(),
                        'cart_comment_end_date'=> $attr->getCartCommentEndDate(),
                        'cart_comment_start_date' => $attr->getCartCommentStartDate(),
                        'color' => $attr->getColor(),
                        'on_sale' => $attr->getOnSale()
                    );

                    $product->addData($attrs);
                }
            }

        }catch (Exception $e){
            Mage::logException($e);
        }

       return $this;
    }

    public function setShippingCommentToQuote($observer)
    {
        $event = $observer->getEvent();
        $request = $event->getRequest();
        $store_code=$request->getPost('cc_storeid', false);

        $instructionType=$request->getPost('delivery-instructions', false);
        $orderComment = $request->getPost('shipping_comment', false);
        $orderInstructionComment=$instructionType.' '.$orderComment;
        $session = Mage::getSingleton('checkout/session');
        //$session->setData('shipping_comment', $orderComment);
        $session->setData('shipping_comment', $orderInstructionComment);
        //echo  $instructionType.':  comment:'.$orderComment;
        //die();
    }

    public function setShippingCommentToOrder($observer)
    {
        $session = Mage::getSingleton('checkout/session');
        $shippingComment = $session->getData('shipping_comment');
        if($shippingComment){
            try{
                $status = Mage::getModel('sales/order_status_history')
                    ->setStatus("pending")
                    ->setComment($shippingComment)
                    ->setIsCustomerNotified(false);
                $observer->getEvent()->getOrder()->addStatusHistory($status);
            }catch(Exception $e) {

            }
        }
    }
}