<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 8/21/13
 * Time: 11:58 AM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_Location_Block_Store_View extends Mage_Core_Block_Template
{

    protected $_store;

    protected $_storeInfo;

    public function _construct()
    {
        $this->_store = Mage::registry('current_store');
    }

    protected function _prepareLayout()
    {

        $this->_storeInfo = $this->_store->loadInfo();

        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->setTitle($this->_storeInfo->getMetaTitle());
            $head->setKeywords($this->_storeInfo->getMetaKeywords());
            $head->setDescription($this->_storeInfo->getMetaDescription());
        }

        return parent::_prepareLayout();
    }

    public function getStore()
    {
        return $this->_store;
    }

    public function getStoreInfo()
    {
        return $this->_storeInfo;
    }

    public function getStoreImg($image)
    {
        $filePath = Mage::getBaseDir('media') . '/location/'.$image;

        if(file_exists($filePath)){

            return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA). '/location/'.$image;
        }

        return '';
    }

}