<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gayan
 * Date: 7/24/13
 * Time: 8:42 AM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_Seo_Block_System_Config_Backend_Downloadseo extends  Mage_Adminhtml_Block_System_Config_Form_Field{

    /**
     * Get the system config field and insert a HTML link
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $storeCode = Mage::app()->getRequest()->getParam('store');
        $store = Mage::getModel("core/store")->load($storeCode);
        $storeId = $store->getId();

        if (Mage::getStoreConfig('nswebredirects/seomassupdate/upload',$storeId ))
        {
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'netstarter/seo/' . Mage::getStoreConfig('nswebredirects/seomassupdate/upload',$storeId);
            $html = "<a href='" . $url . "'>Download</a>";
        }
        else
        {
            $html = "No CSV file provided.";
        }
        return $html;
    }

}