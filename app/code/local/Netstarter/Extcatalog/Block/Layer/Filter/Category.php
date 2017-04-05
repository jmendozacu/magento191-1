<?php
class Netstarter_Extcatalog_Block_Layer_Filter_Category extends Mage_Catalog_Block_Layer_Filter_Category
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/layer/filter_category.phtml');
    }

    public function getCategoryName()
    {
        return $this->_filter->getCategory()->getName();
    }

    public function getLookBookTitle()
    {
        return Mage::helper('catalog')->__("Find the Look that's you");
    }

    public function getVar()
    {
        return $this->_filter->getRequestVar();
    }
}
