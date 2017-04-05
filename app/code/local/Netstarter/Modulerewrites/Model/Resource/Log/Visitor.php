<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 11/26/13
 * Time: 11:59 AM
 * Log visitor overridden
 */ 
class Netstarter_Modulerewrites_Model_Resource_Log_Visitor extends Mage_Log_Model_Resource_Visitor
{

    protected function _beforeSave(Mage_Core_Model_Abstract $visitor)
    {
        if (!$this->_urlLoggingCondition->isLogEnabled()) {
            return $this;
        }
//        if (!$visitor->getIsNewVisitor()) {
//            $this->_saveUrlInfo($visitor);
//        }
        return $this;
    }

    /**
     * Actions after save
     *
     * @param Mage_Core_Model_Abstract $visitor
     * @return Mage_Log_Model_Resource_Visitor
     */
    protected function _afterSave(Mage_Core_Model_Abstract $visitor)
    {
        if ($this->_urlLoggingCondition->isLogDisabled()) {
            return $this;
        }
        if ($visitor->getIsNewVisitor()) {
            if ($this->_urlLoggingCondition->isVisitorLogEnabled()) {
//                $this->_saveVisitorInfo($visitor);
                $visitor->setIsNewVisitor(false);
            }
        } else {
            if ($this->_urlLoggingCondition->isLogEnabled()) {
//                $this->_saveVisitorUrl($visitor);
                if ($visitor->getDoCustomerLogin() || $visitor->getDoCustomerLogout()) {
                    $this->_saveCustomerInfo($visitor);
                }
            }
            if ($this->_urlLoggingCondition->isVisitorLogEnabled()) {
                if ($visitor->getDoQuoteCreate() || $visitor->getDoQuoteDestroy()) {
                    $this->_saveQuoteInfo($visitor);
                }
            }
        }
        return $this;
    }
}
