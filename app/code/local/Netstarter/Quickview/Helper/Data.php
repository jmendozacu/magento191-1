<?php
/**
 * Created by Netstarter Pty Ltd.
 * User: Dilhan Maduranga
 * Date: 5/21/13
 * Time: 10:58 AM
 */
class Netstarter_Quickview_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * @method  isModuleEnabled
     *          Check wheather module is enabled or not
     * @return  bool
     */
    public function isModuleEnabled($moduleName='')
    {
        $enabled = true;
        if ($moduleName) {
            if (!Mage::helper('core')->isModuleEnabled($moduleName)) {
                $enabled = false;
            }
        }
        return $enabled;
    }

    public function getQuickViewUrl($productId, $type = null)
    {
        $url = '';
        if ($productId) {
                $type = ($type == 'GROUP')?'quickview/product/group':'quickview/product';
                $url =  Mage::getUrl($type, array('productId' => $productId)).'?isAjax=1';
        }
        return $url;
    }
}