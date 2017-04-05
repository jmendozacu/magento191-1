<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 8/15/13
 * Time: 11:30 AM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_Location_Block_View extends Mage_Core_Block_Template
{

    protected $_storeInfo;

    public function _construct()
    {

        $this->_storeInfo = Mage::getResourceModel('location/main')
            ->getConfigValues('content', Mage::app()->getStore()->getId(), true);
    }

    protected function _prepareLayout()
    {
        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->setTitle($this->_storeInfo->getMetaTitle());
            $head->setKeywords($this->_storeInfo->getMetaKeywords());
            $head->setDescription($this->_storeInfo->getMetaDescription());
        }

        return parent::_prepareLayout();
    }

    public function getStoreInfo()
    {
        return $this->_storeInfo;
    }


}
