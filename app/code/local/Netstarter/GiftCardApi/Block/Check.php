<?php
class Netstarter_GiftCardApi_Block_Check extends Mage_Core_Block_Template
{

    private $_message = null;

    public function getCode()
    {
        return $this->getRequest()->getParam('giftcard_code', '');
    }

    public function getPin()
    {
        return $this->getRequest()->getParam('giftcard_pin', '');
    }

    public function getErrorMessage()
    {
        return $this->_message;
    }

    public function getCard()
    {
        try{
            $cardProcessor = Mage::getModel('giftcardapi/process')->processGiftCard($this->getCode(), $this->getPin());

            $card = $cardProcessor->checkBalance();
            return $card;

        }catch (Exception $e){

            $this->_message = $e->getMessage();
        }
    }
}
