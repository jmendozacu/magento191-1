<?php
/**
* 
*/
class Netstarter_Afeature_Block_View extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{

//    public function __construct(){
//        die();
//    }

    public function getSelectedBannerIds()
    {
        $ids = array();
        if ($this->_getData('banner_ids')) {
            $ids = explode(',',  $this->_getData('banner_ids'));
        }
        return $ids;
    }

    public function getAll()
    {

        $ids = $this->getSelectedBannerIds();
        if($ids) {

            $idsStr  = $this->_getData('banner_ids');

            $banners =  Mage::getModel('afeature/afeature')->getCollection();
            $banners->getSelect()->where("afeature_id IN ($idsStr) AND active = 1")
                    ->order(new Zend_Db_Expr("FIELD(afeature_id, $idsStr)"));

            return $banners;
        }
    }
    
    public function getMainImageUrl($obj)
    {
        if (!$obj->getImageUrl()) return false;

        $imageUrl = Mage::getBaseDir ( 'media' ) . DS . "afeature" . DS . 'main' . DS . $obj->getImageUrl();

        if(file_exists($imageUrl)) {

            return Mage::getBaseUrl ( 'media' ) . "afeature/main/".$obj->getImageUrl();
        } else {
            return '';
        }
    }

    public function getMobileImageUrl($obj)
    {
        if(! $obj->getMobileImageUrl ()) return false;

        $mobileImageUrl = Mage::getBaseDir( 'media' ) . DS . "afeature" . DS . 'mobile' . DS . $obj->getMobileImageUrl();

        if(file_exists($mobileImageUrl)){
            return Mage::getBaseUrl ( 'media' ) . "afeature/mobile/". $obj->getMobileImageUrl();
        } else {
            return '';
        }
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }
}