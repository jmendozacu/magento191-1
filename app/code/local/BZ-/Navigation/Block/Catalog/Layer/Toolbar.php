<?php

/**
 * Class Toolbar in order to remove the ajax query in the url
 * @param 
 * @package BZ_Toolbar
 * @author Ben Zhang <ben_zhanghf@hotmail.com>
 */
class BZ_Navigation_Block_Catalog_Layer_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    protected $_direction           = 'desc';

    public function getPagerUrl($params=array())
    {
        $urlParams = array();
        $urlParams['_current']  = false;//not use current will not keep the useless other query
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        $urlParams['_query']    = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }
    
    public function getPagerHtml()
    {
        $pagerBlock = $this->getChild('product_list_pager');

        if ($pagerBlock instanceof Varien_Object) {

            /* @var $pagerBlock Mage_Page_Block_Html_Pager */
            $pagerBlock->setAvailableLimit($this->getAvailableLimit());

            $pagerBlock->setUseContainer(false)
                ->setShowPerPage(false)
                ->setShowAmounts(false)
                ->setLimitVarName($this->getLimitVarName())
                ->setPageVarName($this->getPageVarName())
                ->setLimit($this->getLimit())
                ->setFrameLength(Mage::getStoreConfig('design/pagination/pagination_frame'))
                ->setJump(Mage::getStoreConfig('design/pagination/pagination_frame_skip'))
                ->setCollection($this->getCollection());

            return $pagerBlock->toHtml();
        }

        return '';
    }
}
