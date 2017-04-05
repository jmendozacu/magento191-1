<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Page
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Store and language switcher block
 *
 * @category   Mage
 * @package    Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Netstarter_Modulerewrites_Block_Page_Switch extends Mage_Page_Block_Switch
{

    /**
     * method: getWebsiteStores
     * Get the base urls of all the active stores for all the websites
     * @return array
     */
    public function getWebsiteStores() {
        $activeStores = array();
        $websites = Mage::app()->getWebsites();
        if ($websites) {
            foreach($websites as $website) {
                $stores = $website->getStores();
                if ($stores) {
                    foreach ($stores as $store) {
                        if ($store->getIsActive()) {
                            $store->setLocaleCode(Mage::getStoreConfig('general/locale/code', $store->getId()));

                            $baseUrl = $store->getBaseUrl();
                            $store->setHomeUrl($baseUrl);
                            $activeStores[$store->getId()] = $store;
                        }
                    }
                }
            }
        }
        return $activeStores;
    }
}
