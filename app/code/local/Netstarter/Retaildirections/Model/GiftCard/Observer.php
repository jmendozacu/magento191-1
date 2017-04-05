<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 10/7/13
 * Time: 8:11 AM
 * To change this template use File | Settings | File Templates.
 */ 
class Netstarter_Retaildirections_Model_GiftCard_Observer extends Enterprise_GiftCard_Model_Observer
{

    /**
     * had to override the method. it is not possible handle this with observers
     *
     * @param Varien_Event_Observer $observer
     * @return $this|Enterprise_GiftCard_Model_Observer
     */
    public function generateGiftCardAccounts(Varien_Event_Observer $observer)
    {
        // sales_order_save_after

        $order = $observer->getEvent()->getOrder();
        $requiredStatus = Mage::getStoreConfig(
            Enterprise_GiftCard_Model_Giftcard::XML_PATH_ORDER_ITEM_STATUS,
            $order->getStore());
        $loadedInvoices = array();

        /**
         * added an order id check, because sometimes we get integrity constraint issue
         */
        if($order && $order->getEntityId()){

            foreach ($order->getAllItems() as $item) {
                if ($item->getProductType() == Enterprise_GiftCard_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD) {
                    $qty = 0;
                    $options = $item->getProductOptions();

                    switch ($requiredStatus) {
                        case Mage_Sales_Model_Order_Item::STATUS_INVOICED:
                            $paidInvoiceItems = (isset($options['giftcard_paid_invoice_items'])
                                ? $options['giftcard_paid_invoice_items']
                                : array());
                            // find invoice for this order item
                            $invoiceItemCollection = Mage::getResourceModel('sales/order_invoice_item_collection')
                                ->addFieldToFilter('order_item_id', $item->getId());

                            foreach ($invoiceItemCollection as $invoiceItem) {
                                $invoiceId = $invoiceItem->getParentId();
                                if(isset($loadedInvoices[$invoiceId])) {
                                    $invoice = $loadedInvoices[$invoiceId];
                                } else {
                                    $invoice = Mage::getModel('sales/order_invoice')
                                        ->load($invoiceId);
                                    $loadedInvoices[$invoiceId] = $invoice;
                                }
                                // check, if this order item has been paid
                                if ($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_PAID &&
                                    !in_array($invoiceItem->getId(), $paidInvoiceItems)
                                ) {
                                    $qty += $invoiceItem->getQty();
                                    $paidInvoiceItems[] = $invoiceItem->getId();
                                }
                            }
                            $options['giftcard_paid_invoice_items'] = $paidInvoiceItems;
                            break;
                        default:
                            $qty = $item->getQtyOrdered();
                            if (isset($options['giftcard_created_codes'])) {
                                $qty -= count($options['giftcard_created_codes']);
                            }
                            break;
                    }

                    $hasFailedCodes = false;
                    if ($qty > 0) {
                        $isRedeemable = 0;
                        if ($option = $item->getProductOptionByCode('giftcard_is_redeemable')) {
                            $isRedeemable = $option;
                        }

                        $lifetime = 0;
                        if ($option = $item->getProductOptionByCode('giftcard_lifetime')) {
                            $lifetime = $option;
                        }

                        $amount = $item->getBasePrice();
                        $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();

                        $data = new Varien_Object();
                        $data->setWebsiteId($websiteId)
                            ->setAmount($amount)
                            ->setLifetime($lifetime)
                            ->setIsRedeemable($isRedeemable)
                            ->setOrderItem($item);

                        $codes = (isset($options['giftcard_created_codes']) ?
                            $options['giftcard_created_codes'] : array());

                        /**
                         * Netstarter change : to add giftcard object
                         */
                        $codesObj = (isset($options['giftcard_created_codes_obj']) ?
                            $options['giftcard_created_codes_obj'] : array());

                        $goodCodes = 0;
                        for ($i = 0; $i < $qty; $i++) {
                            try {
                                $code = new Varien_Object();
                                /**
                                 * Netstarter change : new dispatch event
                                 */
                                Mage::dispatchEvent('enterprise_giftcardaccount_create_before',
                                    array('request'=>$data, 'code'=>$code));

                                Mage::dispatchEvent('enterprise_giftcardaccount_create',
                                    array('request'=>$data, 'code'=>$code));
                                $codes[] = $code->getCode();
                                $codesObj[] = $code;

                                $goodCodes++;
                            } catch (Mage_Core_Exception $e) {
                                $hasFailedCodes = true;
                                $codes[] = null;
                            }
                        }
                        if ($goodCodes && $item->getProductOptionByCode('giftcard_recipient_email')) {
                            $sender = $item->getProductOptionByCode('giftcard_sender_name');
                            $senderName = $item->getProductOptionByCode('giftcard_sender_name');
                            if ($senderEmail = $item->getProductOptionByCode('giftcard_sender_email')) {
                                $sender = "$sender <$senderEmail>";
                            }
                            /**
                             * Netstarter change : send the giftcard object (code + pin) rather than the code string
                             */
                            $codeList = Mage::helper('enterprise_giftcard')->getEmailGeneratedItemsBlock()
                                ->setCodes($codesObj)
                                ->setIsRedeemable($isRedeemable)
                                ->setStore(Mage::app()->getStore($order->getStoreId()));
                            $balance = Mage::app()->getLocale()->currency(
                                Mage::app()->getStore($order->getStoreId())
                                    ->getBaseCurrencyCode())->toCurrency($amount);
                            $_product = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
                            $product_image = (string)Mage::helper('catalog/image')->init($_product, 'image');
                            $templateData = array(
                                'name'                   => $item->getProductOptionByCode('giftcard_recipient_name'),
                                'email'                  => $item->getProductOptionByCode('giftcard_recipient_email'),
                                'sender_name_with_email' => $sender,
                                'sender_name'            => $senderName,
                                'gift_message'           => $item->getProductOptionByCode('giftcard_message'),
                                'giftcards'              => $codeList->toHtml(),
                                'balance'                => $balance,
                                'Image_url'              => $product_image,
                                'is_multiple_codes'      => 1 < $goodCodes,
                                'store'                  => $order->getStore(),
                                'store_name'             => $order->getStore()->getName(),//@deprecated after 1.4.0.0-beta1
                                'is_redeemable'          => $isRedeemable,
                            );

                            $email = Mage::getModel('core/email_template')
                                ->setDesignConfig(array('store' => $item->getOrder()->getStoreId()));
                            $email->sendTransactional(
                                $item->getProductOptionByCode('giftcard_email_template'),
                                Mage::getStoreConfig(
                                    Enterprise_GiftCard_Model_Giftcard::XML_PATH_EMAIL_IDENTITY,
                                    $item->getOrder()->getStoreId()),
                                $item->getProductOptionByCode('giftcard_recipient_email'),
                                $item->getProductOptionByCode('giftcard_recipient_name'),
                                $templateData
                            );

                            if ($email->getSentSuccess()) {
                                $options['email_sent'] = 1;
                            }
                        }
                        $options['giftcard_created_codes'] = $codes;
                        /**
                         * Netstarter change : save giftcard object
                         */
                        $options['giftcard_created_codes_obj'] = $codesObj;
                        $item->setProductOptions($options);
                        $item->save();
                    }
                    if ($hasFailedCodes) {
                        $url = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/giftcardaccount');
                        $message = Mage::helper('enterprise_giftcard')->__('Some of Gift Card Accounts were not generated properly. You can create Gift Card Accounts manually <a href="%s">here</a>.', $url);

                        Mage::getSingleton('adminhtml/session')->addError($message);
                    }
                }
            }
        }

        return $this;
    }
}