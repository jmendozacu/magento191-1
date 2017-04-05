<?php

class Netstarter_Tbyb_Block_Email_Totals extends Mage_Core_Block_Template
{
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        
        $paynow = Mage::getModel("netstarter_tbyb/total_paynow");
        $paylater = Mage::getModel("netstarter_tbyb/total_paylater");
        
        $quote = Mage::getModel("sales/quote")->load($parent->getSource()->getQuoteId());
        
        if (!$paynow->isTbybCart ($quote->getBillingAddress()))
        {
            return;
        }
        
        $paynow->setUseOrderDate($parent->getSource()->getCreatedAt());
        $paylater->setUseOrderDate($parent->getSource()->getCreatedAt());

        $totalsPayNow   = $paynow->fetch($quote->getBillingAddress())->getLatestCalculated();
        $totalsPayLater = $paylater->fetch($quote->getBillingAddress())->getLatestCalculated();

        $parent->addTotalBefore(new Varien_Object(array(
                'code'  => 'netstarter_tbyb_paylater',
                'field' => 'netstarter_tbyb_paylater',
                'value' => $totalsPayLater['value'],
                'label' => $totalsPayLater['title']
        )), "grand_total");

        $parent->addTotalBefore(new Varien_Object(array(
                'code'  => 'netstarter_tbyb_paynow',
                'field' => 'netstarter_tbyb_paynow',
                'value' => $totalsPayNow['value'],
                'label' => $totalsPayNow['title']
        )), "netstarter_tbyb_paylater");
    }
}
