<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 10/2/13
 * Time: 12:19 PM
 *
 */ 
class Netstarter_Retaildirections_Model_GiftCardAccount_Observer extends Enterprise_GiftCardAccount_Model_Observer
{

    /**
     * Rewritten to add gift card code if it's already there
     *
     * @param Varien_Event_Observer $observer
     * @return $this|Enterprise_GiftCardAccount_Model_Observer
     */
    public function create(Varien_Event_Observer $observer)
    {
        $data = $observer->getEvent()->getRequest();
        $code = $observer->getEvent()->getCode();
        if ($data->getOrder()) {
            $order = $data->getOrder();
        } elseif ($data->getOrderItem()->getOrder()) {
            $order = $data->getOrderItem()->getOrder();
        } else {
            $order = null;
        }

        $model = Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
            ->setStatus(Enterprise_GiftCardAccount_Model_Giftcardaccount::STATUS_ENABLED)
            ->setWebsiteId($data->getWebsiteId())
            ->setBalance($data->getAmount())
            ->setLifetime($data->getLifetime())
            ->setIsRedeemable($data->getIsRedeemable())
            ->setOrder($order);

        if($gfCode = $code->getCode()){

            $model->setCode($gfCode);
            $giftCardCodePoolModel = Mage::getModel('enterprise_giftcardaccount/pool');
            $giftCardCodePoolModel->getResource()->saveCode($gfCode);
        }

        if($pin = $code->getPin()) $model->setPinCode($pin);
        if($code->getRedeem() == 1) $model->setStatus(Enterprise_GiftCardAccount_Model_Giftcardaccount::STATUS_DISABLED);

        $model->save();

        return $this;
    }
}