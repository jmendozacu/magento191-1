<?php
/**
 * @author http://www.netstarter.com.au
 * @licence http://www.netstarter.com.au
 * Netstarter_FitWizard_Model_CouponEmail
 */ 
class Netstarter_FitWizard_Model_CouponEmail extends Mage_Core_Model_Abstract
{

    /**
     * @return Netstarter_FitWizard_Model_CouponEmail
     */
    protected function _construct()
    {
        $this->_init('fitwizard/couponEmail');
    }

    /**
     * @param string $email
     * @return bool
     */
    public function couponEmailExists($email = '')
    {
        $exists = false;
        if (!empty($email)) {
            $collection = $this->getCollection()->addFieldToFilter('email', $email);
            $collection->getSelect()->limit(1);

            if ($collection->getSize() >= 1) {
                $exists = true;
            }
        }
        return $exists;
    }

    /**
     * @param bool $email
     * @param string $couponCode
     * @return $this
     */
    public function saveCouponEmail($email = false, $couponCode='')
    {
        try{
            if ($email) {
                $this->setData('email', trim($email));
                $this->setData('coupon_code', $couponCode);
                $this->setData('created_date', Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                $this->save();
            }
            return $this;
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}