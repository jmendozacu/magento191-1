<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 9/16/13
 * Time: 2:57 PM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_GiftCardApi_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     * @throws Netstarter_GiftCardApi_Exception
     */
    public function processOrderPlace(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $cards = Mage::helper('enterprise_giftcardaccount')->getCards($order);
        if (is_array($cards)) {

            $cardsByType = array();
            foreach ($cards as $card) {
                if(!is_null($card['t']))
                    $cardsByType[$card['t']][] = $card;
            }

            if(!empty($cardsByType)){

                $checkoutSession = Mage::getSingleton('checkout/session');
                $cardProcessor = Mage::getModel('giftcardapi/process');
                $redeemRequests = $checkoutSession->getGiftRedeemRequests();

                if(is_null($redeemRequests))$redeemRequests = array();

                try{

                    $result = $cardProcessor->multipleRedeemGiftCard($cardsByType);
                    if($result){
                        $redeemRequests = array_merge($redeemRequests, $result);
                        $checkoutSession->setGiftRedeemRequests($redeemRequests);

                    }else{
                        throw new Netstarter_GiftCardApi_Exception('There has been an error processing your request', 004);
                    }

                }catch (Exception $e){
                    // error detected, need to revert the possible giftcard transaction

                    if(!empty($redeemRequests)){
                        $redeemRequestsReturn = $cardProcessor->cancelGiftCardRedeem($redeemRequests);
                        $checkoutSession->setGiftRedeemRequests($redeemRequestsReturn);
                    }

                    throw new Netstarter_GiftCardApi_Exception($e->getMessage(), $e->getCode());
                }
            }
        }
        return $this;
    }

    /**
     * Revert all redeemed
     */
    public function revertGiftCards()
    {
        try{

            $cardProcessor = Mage::getModel('giftcardapi/process');
            $checkoutSession = Mage::getSingleton('checkout/session');
            $redeemRequests = $checkoutSession->getGiftRedeemRequests();

            if(!empty($redeemRequests)){

                $redeemRequestsReturn = $cardProcessor->cancelGiftCardRedeem($redeemRequests);
                $checkoutSession->setGiftRedeemRequests($redeemRequestsReturn);
            }

        }catch (Exception $e){

            Mage::logException($e);
        }
    }

    /**
     * add custom field to adminhtml_giftcardaccount_edit_tab_info
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function appendCustomColumn(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (!isset($block)) {
            return $this;
        }
        if ($block->getType() == 'enterprise_giftcardaccount/adminhtml_giftcardaccount_edit_tab_info') {

            $form = $block->getForm();
            $model = Mage::registry('current_giftcardaccount');

            $fieldset = $form->getElement('base_fieldset');

            if ($model->getId()){
                $fieldset->addField('pin_code', 'label', array(
                    'name'      => 'pin_code',
                    'label'     => Mage::helper('enterprise_giftcardaccount')->__('Pin Number'),
                    'title'     => Mage::helper('enterprise_giftcardaccount')->__('Pin Number'),
                    'value'     => $model->getPinCode()
                ));

                if($model->getAdditional()){

                    $fieldset->addField('additional', 'label', array(
                        'name'      => 'additional',
                        'label'     => Mage::helper('enterprise_giftcardaccount')->__('Additional'),
                        'title'     => Mage::helper('enterprise_giftcardaccount')->__('Additional'),
                        'value'     => $model->getAdditional()
                    ));
                }
            }
        }
    }
}