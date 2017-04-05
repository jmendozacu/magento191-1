<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Encrypted config field backend model
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Netstarter_Globalpassword_Model_System_Hash extends Mage_Core_Model_Config_Data
{
    /**
     * Decrypt value after loading
     *
     */
    protected function _afterLoad()
    {
//        $value = (string)$this->getValue();
//        if (!empty($value) && ($decrypted = Mage::helper('core')->decrypt($value))) {
//            $this->setValue($decrypted);
//        }
    }

    /**
     * Encrypt value before saving
     *
     */
    protected function _beforeSave()
    {
        $value = (string)$this->getValue();
        // don't change value, if an obscured value came
        if (preg_match('/^\*+$/', $this->getValue()) && !empty($value)) {
            $value = $this->getOldValue();
            $this->setValue($value);
            return;
        }
        if (!empty($value) && ($encrypted = Mage::helper('core')->getHash($value, true))) {
            $this->setValue($encrypted);
        }
    }
}