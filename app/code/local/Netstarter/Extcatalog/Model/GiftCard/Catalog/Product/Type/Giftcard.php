<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 9/4/13
 * Time: 3:55 PM
 * To change this template use File | Settings | File Templates.
 */ 
class Netstarter_Extcatalog_Model_GiftCard_Catalog_Product_Type_Giftcard extends Enterprise_GiftCard_Model_Catalog_Product_Type_Giftcard {

    private function _validate(Varien_Object $buyRequest, $product, $processMode)
    {
        $product = $this->getProduct($product);
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);

        $allowedAmounts = array();
        foreach ($product->getGiftcardAmounts() as $value) {
            $allowedAmounts[] = Mage::app()->getStore()->roundPrice($value['website_value']);
        }

        $allowOpen = $product->getAllowOpenAmount();
        $minAmount = $product->getOpenAmountMin();
        $maxAmount = $product->getOpenAmountMax();


        $selectedAmount = $buyRequest->getGiftcardAmount();
        $customAmount = $buyRequest->getCustomGiftcardAmount();

        $rate = Mage::app()->getStore()->getCurrentCurrencyRate();
        if ($rate != 1) {
            if ($customAmount) {
                $customAmount = Mage::app()->getLocale()->getNumber($customAmount);
                if (is_numeric($customAmount) && $customAmount) {
                    $customAmount = Mage::app()->getStore()->roundPrice($customAmount/$rate);
                }
            }
        }

        $emptyFields = 0;
        if (!$buyRequest->getGiftcardRecipientName()) {
            $emptyFields++;
        }
        if (!$buyRequest->getGiftcardSenderName()) {
            $emptyFields++;
        }

        if (!$this->isTypePhysical($product)) {
            if (!$buyRequest->getGiftcardRecipientEmail()) {
                $emptyFields++;
            }
            if (!$buyRequest->getGiftcardSenderEmail()) {
                $emptyFields++;
            }
        }

        if (($selectedAmount == 'custom' || !$selectedAmount) && $allowOpen && $customAmount <= 0) {
            $emptyFields++;
        } else if (is_numeric($selectedAmount)) {
            if (!in_array($selectedAmount, $allowedAmounts)) {
                $emptyFields++;
            }
        } else if (count($allowedAmounts) != 1) {
            $emptyFields++;
        }

        if ($emptyFields > 1 && $isStrictProcessMode) {
            Mage::throwException(
                Mage::helper('enterprise_giftcard')->__('Please specify all the required information.')
            );
        }

        $amount = null;
        if (($selectedAmount == 'custom' || !$selectedAmount) && $allowOpen) {
            if ($customAmount <= 0 && $isStrictProcessMode) {
                Mage::throwException(
                    Mage::helper('enterprise_giftcard')->__('Please specify Gift Card amount.')
                );
            }
            if (!$minAmount || ($minAmount && $customAmount >= $minAmount)) {
                if (!$maxAmount || ($maxAmount && $customAmount <= $maxAmount)) {
                    $amount = $customAmount;
                } else if ($customAmount > $maxAmount && $isStrictProcessMode) {
                    $messageAmount = Mage::helper('core')->currency($maxAmount, true, false);
                    Mage::throwException(
                        Mage::helper('enterprise_giftcard')->__('Gift Card max amount is %s', $messageAmount)
                    );
                }
            } else if ($customAmount < $minAmount && $isStrictProcessMode) {
                $messageAmount = Mage::helper('core')->currency($minAmount, true, false);
                Mage::throwException(
                    Mage::helper('enterprise_giftcard')->__('Gift Card min amount is %s', $messageAmount)
                );
            }
        } else if (is_numeric($selectedAmount)) {
            if (in_array($selectedAmount, $allowedAmounts)) {
                $amount = $selectedAmount;
            }
        }
        if (is_null($amount)) {
            if (count($allowedAmounts) == 1) {
                $amount = array_shift($allowedAmounts);
            }
        }

        if (is_null($amount) && $isStrictProcessMode) {
            Mage::throwException(
                Mage::helper('enterprise_giftcard')->__('Please specify Gift Card amount.')
            );
        }

        if (!$buyRequest->getGiftcardRecipientName() && $isStrictProcessMode) {
            Mage::throwException(
                Mage::helper('enterprise_giftcard')->__('Please specify recipient name.')
            );
        }
        if (!$buyRequest->getGiftcardSenderName() && $isStrictProcessMode) {
            Mage::throwException(
                Mage::helper('enterprise_giftcard')->__('Please specify sender name.')
            );
        }

        if (!$this->isTypePhysical($product)) {
            if (!$buyRequest->getGiftcardRecipientEmail() && $isStrictProcessMode) {
                Mage::throwException(
                    Mage::helper('enterprise_giftcard')->__('Please specify recipient email.')
                );
            }
            if (!$buyRequest->getGiftcardSenderEmail() && $isStrictProcessMode) {
                Mage::throwException(
                    Mage::helper('enterprise_giftcard')->__('Please specify sender email.')
                );
            }
        }

        return $amount;
    }

    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        $result = Mage_Catalog_Model_Product_Type_Abstract::_prepareProduct($buyRequest, $product, $processMode);

        if (is_string($result)) {
            return $result;
        }

        try {
            $amount = $this->_validate($buyRequest, $product, $processMode);
        } catch (Mage_Core_Exception $e) {
            return $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            return Mage::helper('enterprise_giftcard')->__('An error has occurred while preparing Gift Card.');
        }

        if(isset($buyRequest['change_type'])){
            if($buyRequest['change_type'] == Enterprise_GiftCard_Model_Giftcard::TYPE_PHYSICAL){

                $product->setGiftcardType(Enterprise_GiftCard_Model_Giftcard::TYPE_PHYSICAL);
            }elseif($buyRequest['change_type'] == Enterprise_GiftCard_Model_Giftcard::TYPE_VIRTUAL){

                $product->setGiftcardType(Enterprise_GiftCard_Model_Giftcard::TYPE_VIRTUAL);
            }
        }

        $product->addCustomOption('giftcard_amount', $amount, $product);

        $product->addCustomOption('giftcard_sender_name', $buyRequest->getGiftcardSenderName(), $product);
        $product->addCustomOption('giftcard_recipient_name', $buyRequest->getGiftcardRecipientName(), $product);
        if (!$this->isTypePhysical($product)) {
            $product->addCustomOption('giftcard_sender_email', $buyRequest->getGiftcardSenderEmail(), $product);
            $product->addCustomOption('giftcard_recipient_email', $buyRequest->getGiftcardRecipientEmail(), $product);
        }

        $messageAllowed = false;
        if ($product->getUseConfigAllowMessage()) {
            $messageAllowed = Mage::getStoreConfigFlag(Enterprise_GiftCard_Model_Giftcard::XML_PATH_ALLOW_MESSAGE);
        } else {
            $messageAllowed = (int) $product->getAllowMessage();
        }

        if ($messageAllowed) {
            $product->addCustomOption('giftcard_message', $buyRequest->getGiftcardMessage(), $product);
        }

        return $result;
    }

}