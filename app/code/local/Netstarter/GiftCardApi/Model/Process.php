<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 9/11/13
 * Time: 8:37 AM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_GiftCardApi_Model_Process
{

    private $_giftCardModel;
    private $_hasValidCard = false;
    private $_cardCode;
    private $_pin;
    private $_giftCardModels;

    protected function _clearObj()
    {
        $this->_giftCardModel = null;
        $this->__hasValidCard = false;
    }

    public function updateGiftAccount($giftData)
    {
        if($this->_giftCardModel){

            $giftAmount = $giftData['balance'];
            $giftAdditional = $giftData['additional'];

            $giftCardAccountModel = Mage::getModel('enterprise_giftcardaccount/giftcardaccount')->load($this->_cardCode,'code');

            if(!$giftCardAccountModel->getId()){
                $giftCardCodePoolModel = Mage::getModel('enterprise_giftcardaccount/pool');
                $giftCardCodePoolModel->getResource()->saveCode($this->_cardCode);

                $giftCardAccountModel->setCode($this->_cardCode);
                $giftCardAccountModel->setStatus(1);
                $giftCardAccountModel->setDateCreated(Mage::getModel('core/date')->date('Y-m-d'));
                $giftCardAccountModel->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                $giftCardAccountModel->setBalance($giftAmount);
                $giftCardAccountModel->setAdditional($giftAdditional);
                $giftCardAccountModel->setState(0);

                $giftCardAccountModel->setType($this->_giftCardModel->getModelCode());
                $giftCardAccountModel->setPinCode($this->_pin);
                $giftCardAccountModel->setPinCode($this->_pin);
                $giftCardAccountModel->setIsRedeemable(1);
                $giftCardAccountModel->save();
            }else{

                $giftCardAccountModel->setBalance($giftAmount);
                $giftCardAccountModel->setType($this->_giftCardModel->getModelCode());
                $giftCardAccountModel->setPinCode($this->_pin);
                $giftCardAccountModel->setAdditional($giftAdditional);
                if($giftAmount) $giftCardAccountModel->setState(0);
                $giftCardAccountModel->save();
            }

            return $giftCardAccountModel;
        }

        return null;
    }

    private function _updateQuote($giftCardAccountModel)
    {
        if($giftCardAccountModel instanceof Enterprise_GiftCardAccount_Model_Giftcardaccount){

            $giftCardAccountModel->addToCart();

            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $quote->collectTotals();
            $quote->save();

            return $quote->getGrandTotal();
        }

        return null;
    }


    public function hasValidCard()
    {
        return $this->_hasValidCard;
    }

    protected function _Model($code, $config, $store = null)
    {
        if (!isset($config['model'])) {
            return false;
        }
        $modelName = $config['model'];

        try {
            $giftCard = Mage::getModel($modelName);
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $giftCard;
    }

    public function getActiveGiftCards($store = null)
    {
        if(!$this->_giftCardModels){

            $giftCards = array();
            $config = Mage::getStoreConfig('giftcardsapi', $store);
            foreach ($config as $code => $carrierConfig) {
                if (Mage::getStoreConfigFlag('giftcardsapi/'.$code.'/active', $store)) {
                    $giftCardModel = $this->_Model($code, $carrierConfig, $store);
                    if ($giftCardModel) {
                        $giftCards[$code] = $giftCardModel;
                    }
                }
            }
            $this->_giftCardModels = $giftCards;
        }

        return $this->_giftCardModels;
    }

    public function processGiftCard($cardCode, $pin = null, $type = null)
    {
        $giftCardModels = $this->getActiveGiftCards();

        $this->_cardCode = $cardCode;
        $this->_pin = $pin;

        if(!is_null($type) && array_key_exists($type, $giftCardModels)){

            $this->_giftCardModel = $giftCardModels[$type];
            $this->_hasValidCard = true;

        }else{

            foreach($giftCardModels as $code => $giftCardModel){

                $giftCardModel->setGiftCardCode($this->_cardCode);
                $giftCardModel->setPinCode($this->_pin);

                if($giftCardModel->validate()){

                    $this->_giftCardModel = $giftCardModel;
                    $this->_hasValidCard = true;

                    break;
                }
            }
        }

        if(!$this->_giftCardModel)
            throw new Netstarter_GiftCardApi_Exception('Invalid Gift Card code', 000);

        return $this;
    }

    public function checkBalance()
    {
        $giftData = $this->_giftCardModel->checkBalance();
        $giftCardAccountModel = $this->updateGiftAccount($giftData);

        return $giftCardAccountModel;
    }

    public function removeGiftCard($gid)
    {
        $giftCardAccountModel = Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
            ->load($gid);

        if($giftCardAccountModel instanceof Enterprise_GiftCardAccount_Model_Giftcardaccount){

            $giftCardAccountModel->removeFromCart();

            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $quote->collectTotals();
            $quote->save();

            return $quote->getGrandTotal();
        }
    }

    public function apply()
    {
        $giftCardAccountModel = $this->checkBalance();

        if($giftCardAccountModel && $balanceAmount = $giftCardAccountModel->getBalance()){

            if($balanceAmount > 0.0000){

//                $giftCardAccountModel = $this->updateGiftAccount($balanceAmount);
                $grandTotal = $this->_updateQuote($giftCardAccountModel);

                if(!is_null($grandTotal)){

                    return $grandTotal;
                }else{
                    throw new Netstarter_GiftCardApi_Exception('There has been an error processing your request', 001);
                }
            }else{
                throw new Netstarter_GiftCardApi_Exception("Gift card (#{$this->_cardCode}) balance is 0", 002);
            }
        }

        throw new Netstarter_GiftCardApi_Exception('There has been an error processing your request', 001);
    }

    public function redeemGiftCard($amount)
    {
        if($this->_giftCardModel){

           $transactionId = $this->_giftCardModel->redeemGiftCard($amount);

           if($transactionId){

           }
        }
    }

    public function multipleRedeemGiftCard($cardsType)
    {
        $redeemResults = array();
        $this->_clearObj();

        foreach($cardsType as $type=>$cards){

            $this->processGiftCard(null, null, $type);

            if($this->_giftCardModel){

                $transactionResponse = $this->_giftCardModel->multipleRedeemGiftCard($cards);

                if($transactionResponse){

                    $redeemResults[$type] = $transactionResponse;
                }
            }
        }

        return $redeemResults;
    }

    public function cancelGiftCardRedeem($cardsType)
    {
        $this->_clearObj();

        foreach($cardsType as $type=> $transIds){

            $this->processGiftCard(null, null, $type);

            if($this->_giftCardModel){

                $result = $this->_giftCardModel->cancelGiftCardRedeem($transIds);

                unset($cardsType[$type]);
            }
        }

        return $cardsType;
    }
}