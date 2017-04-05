<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Amp
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

class Plumrocket_Amp_Block_Review_Helper extends Mage_Review_Block_Helper
{
    public function _construct()
    {
        $helper = Mage::helper('pramp');
        if ($helper->isAmpRequest()) {
            $this->_availableTemplates['default']= 'pramp/review/helper/summary.phtml';
            $this->_availableTemplates['short']= 'pramp/review/helper/summary_short.phtml';
        }

        parent::_construct();
    }

	public function getModuleName()
    {
        return 'Mage_Review';
    }

}