<?php
/**
 * Class State
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Block_Catalog_Layer_State extends Mage_Catalog_Block_Layer_State
{
    /**
     * clean all url
     */
    public function getClearUrl()
    {
        $url_route = Mage::app()->getRequest()->getAlias('CATALOG_CATEGORY_SHORT_PATH');
        if ($url_route)
            $url = Mage::getBaseUrl() . $url_route;
        else
            $url = Mage::getUrl('*/*/*', array('_current' => false, '_use_rewrite' => true));
        return $url;
    }
}
