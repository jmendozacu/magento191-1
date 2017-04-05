<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 9/16/13
 * Time: 2:45 PM
 * To change this template use File | Settings | File Templates.
 */ 
class Netstarter_GiftCardApi_Model_GiftCardAccount_Giftcardaccount
    extends Enterprise_GiftCardAccount_Model_Giftcardaccount {

    public function addToCart($saveQuote = true, $quote = null)
    {
        if (is_null($quote)) {
            $quote = $this->_getCheckoutSession()->getQuote();
        }
        $website = Mage::app()->getStore($quote->getStoreId())->getWebsite();
        if ($this->isValid(true, true, $website)) {
            $cards = Mage::helper('enterprise_giftcardaccount')->getCards($quote);
            if (!$cards) {
                $cards = array();
            } else {
                foreach ($cards as $one) {
                    if ($one['i'] == $this->getId()) {
                        Mage::throwException(Mage::helper('enterprise_giftcardaccount')->__('This gift card account is already in the quote.'));
                    }
                }
            }
            $cards[] = array(
                'i'=>$this->getId(),        // id
                'c'=>$this->getCode(),      // code
                'a'=>$this->getBalance(),   // amount
                'ba'=>$this->getBalance(),  // base amount
                'p'=>$this->getPinCode(),  // pin code
                't'=>$this->getType(),  // type
                'e'=>$this->getAdditional() // additional
            );
            Mage::helper('enterprise_giftcardaccount')->setCards($quote, $cards);

            if ($saveQuote) {
                $quote->save();
            }
        }

        return $this;
    }
}