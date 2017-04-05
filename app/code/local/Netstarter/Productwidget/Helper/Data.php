<?php
/**
 * Created by Netstarter Pty Ltd.
 * User: Dilhan Maduranga
 * Date: 5/21/13
 * Time: 10:58 AM
 */
class Netstarter_Productwidget_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Dependant Module for the Bestseller
     * @var array
     */
    protected $dependModules = array(
                                    'Netstarter_Quickview' => "Netstarter Quickview"
                                );

    /**
     * @method  isBestSellerDependModulesEnabled
     *          if All the dependant modules enabled return true else false;
     * @return  bool
     */
    public function isBestSellerDependModulesEnabled() {
        $enabled = true;
        if ($this->dependModules) {
            foreach ($this->dependModules as $module => $moduleName) {
                if (!Mage::helper('core')->isModuleEnabled($module)) {
                    $enabled = false;
                }
            }
        }
        return $enabled;
    }


    public function isModuleEnabled($moduleName='') {
        $enabled = true;
        if ($moduleName) {
            if (!Mage::helper('core')->isModuleEnabled($moduleName)) {
                $enabled = false;
            }
        }
        return $enabled;
    }

}