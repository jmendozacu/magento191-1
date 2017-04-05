<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 8/21/13
 * Time: 7:42 AM
 *
 */
class Netstarter_Location_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{

    /**
     * Initialize Controller Router
     *
     * @param Varien_Event_Observer $observer
     */
    public function initControllerRouters($observer)
    {
        /* @var $front Mage_Core_Controller_Varien_Front */
        $front = $observer->getEvent()->getFront();

        $front->addRouter('store', $this);
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request)
    {

        if($request->getModuleName() == 'store'){

            $identifier = trim($request->getPathInfo(), '/');

            $paths = explode('/',$identifier);

            if(!empty($paths[1])){

                $store   = Mage::getModel('location/main');
                $locationId = $store->checkIdentifier($paths[1], Mage::app()->getStore()->getId(), 1);
                if (!$locationId) {
                    return false;
                }

                $request->setModuleName('store')
                    ->setControllerName('index')
                    ->setActionName('view')
                    ->setParam('store_id', $locationId);

                return true;
            }
        }
        return false;
    }

}