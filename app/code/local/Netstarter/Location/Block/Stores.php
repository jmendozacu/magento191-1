<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 8/15/13
 * Time: 11:30 AM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_Location_Block_Stores extends Mage_Core_Block_Template
{

    protected $_distances;
    protected $_locationText = null;

    public function getStoreList()
    {
        $filter = null;
        $queryTxt = $this->getData('queryTxt');
        $filter = $this->getFilter();

        if(!$this->isMobile()){

            $filter = (!$this->isAjax() || empty($filter))?$this->helper('location')->getIpGio():$filter;
        }

        $storeList = array();

        if($filter != 'INVALID'){

            $storeList = Mage::getResourceModel('location/main_collection')->getStoresInRange($filter)->load();

            $this->_distances = $storeList->getDistances();
        }else if($filter == 'INVALID' && !is_null($queryTxt)){

            $collection = Mage::getResourceModel('location/main');
            $resultCol = $collection->getSuggestions($queryTxt);

            $firstItem = null;

            if (count($resultCol) > 0) {

                if(!empty($resultCol[0])){

                    $filter = $resultCol[0]['id'];
                    $this->_locationText = "{$resultCol[0]['postcode']} {$resultCol[0]['suburb']} {$resultCol[0]['statecode']}";
                    $storeList = Mage::getResourceModel('location/main_collection')->getStoresInRange($filter)->load();
                    $this->_distances = $storeList->getDistances();
                }
            }
        }


        return $storeList;
    }

    public function getDistances()
    {
        return $this->_distances;
    }

    public function getLocationText()
    {
        return $this->_locationText;
    }

    public function isMobile()
    {

        if(Mage::helper('core')->isModuleEnabled('Netstarter_Mobile')){

           return $this->helper('mobile/detect')->isMobile();
        }

        return false;
    }

    public function isAjax()
    {
        if(Mage::app()->getRequest()->getControllerName() == 'ajax') return true;

        return false;
    }
}
