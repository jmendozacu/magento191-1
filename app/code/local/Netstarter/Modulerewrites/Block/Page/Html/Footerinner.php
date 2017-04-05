<?php

class Netstarter_Modulerewrites_Block_Page_Html_Footerinner extends Mage_Core_Block_Template
{
    protected $_storeAddress;
    protected $_storePhone;

    protected function _construct()
    {
        $this->addData(array(
            'cache_lifetime'=> false,
            'cache_tags'    => array(Mage_Core_Model_Store::CACHE_TAG, Mage_Cms_Model_Block::CACHE_TAG)
        ));
    }

    /**
     * Get the Store Address configured in Admin Panel -> Configurations -> Store Information
     * @return String
     */
    public function getStoreAddress()
    {
        if (!$this->_storeAddress) {
            $this->_storeAddress = Mage::getStoreConfig('general/store_information/address');
        }

        return $this->_storeAddress;
    }

    /**
     * Get the Store Phone configured in Admin Panel -> Configurations -> Store Information
     * @return String
     */
    public function getStorePhone()
    {
        if (!$this->_storePhone) {
            $this->_storePhone = Mage::getStoreConfig('general/store_information/phone');
        }

        return $this->_storePhone;
    }

    /**
     * @return cms/block footer_links html
     */
    public function getFooterInfoLinks()
    {
        return $this->getLayout()->createBlock('cms/block')->setBlockId('footer_links')->toHtml();
    }

    /**
     * Get the $categoryName => $url array for active categoroies under BnT
     * @return Array
     */
    public function getCategoryNames()
    {
        $persistCats = Mage::registry('persist_cats');

        if(!empty($persistCats)){

            $categories = $persistCats;
        }else{
            $categories = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('url_path')
                ->addAttributeToSelect('custom_link_url')
                ->addAttributeToFilter('include_in_menu', 1)
                ->addAttributeToFilter('level',2)
                ->addAttributeToFilter('is_active',array('eq'=>true));
        }

        return $categories;
    }

    public function getCopyright()
    {
        $copyright = Mage::getStoreConfig('design/footer/copyright');
        $replaced = str_replace("{{copyright_year}}", date('Y'), $copyright);
        return $replaced;
    }

    public function getPaymentOptionThumbs()
    {
        return $this->getLayout()->createBlock('cms/block')->setBlockId('footer_paymentoption_thumbs ')->toHtml();
    }

    /**
     * Get the Recipient Contact Email
     * @return string
     */
    public function getContactEmail()
    {
        return Mage::getStoreConfig('contacts/email/recipient_email');
    }
}
