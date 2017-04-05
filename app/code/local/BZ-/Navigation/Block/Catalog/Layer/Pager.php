<?php
/**
 * Class Pager
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Block_Catalog_Layer_Pager extends Mage_Page_Block_Html_Pager
{
    public function getPagerUrl($params=array())
    {
        $urlParams = array();
        $urlParams['_current']  = false;//not use current will not keep the useless other query
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        $urlParams['_query']    = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }
}
