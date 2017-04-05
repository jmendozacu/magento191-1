<?php
class ARW_Ajaxcart_Block_Ajaxcart extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('arw/ajaxcart/js.phtml');
    }
    
    public function getSendUrl()
    {
        $url = $this->getUrl('ajaxcart/ajax/index');
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "")
        {
            $url = str_replace('http:', 'https:', $url);
        }
        return $url;
    }
    
    public function getUpdateUrl()
    {
        $url = $this->getUrl('checkout/cart/updatePost');
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "")
        {
            $url = str_replace('http:', 'https:', $url);
        }
        return $url;
    }
}