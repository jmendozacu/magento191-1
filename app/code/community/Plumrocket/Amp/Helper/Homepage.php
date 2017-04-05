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

class Plumrocket_Amp_Helper_Homepage extends Mage_Core_Helper_Data {

    function getTopLevelCategories()
    {
    	$rootCategoryId = Mage::app()->getStore()->getRootCategoryId();

        return Mage::getModel('catalog/category')
            ->getResourceCollection()
            ->addAttributeToSelect('*')
            ->setOrder('position', 'ASC')
            ->addAttributeToFilter('level', 2)
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('include_in_menu', 1)
            ->addFieldToFilter('path', array('like' => "%/{$rootCategoryId}/%"))
            ->addIsActiveFilter();
    }

}
