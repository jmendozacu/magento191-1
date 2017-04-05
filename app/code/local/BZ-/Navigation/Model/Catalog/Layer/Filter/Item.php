<?php
/**
 * Class Item
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Model_Catalog_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item
{
    /**
     * Get filter item url
     * @return string
     */
    public function getUrl()
    {
        //check enable or not
        $helper = Mage::helper('bz_navigation');
        $request = Mage::app()->getRequest();
        if (!$helper) {
            return parent::getUrl();
        }
        $class_name = get_class($this->getFilter());
        if (strstr($class_name, '_Category')) {
            return $helper->getCategoryItemUrl($request, $this);
        }
        if (strstr($class_name, '_Price') || strstr($class_name, '_Decimal')) {
            return $helper->getPriceItemUrl($request, $this);
        }
        return $helper->getAttributeItemUrl($request,$this);
    }

    /**
     * Get url for remove item from filter
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        //check enable or not
        $helper = Mage::helper('bz_navigation');
        $request = Mage::app()->getRequest();
        if (!$helper) {
            return parent::getRemoveUrl();
        }
        $class_name = get_class($this->getFilter());
        if (strstr($class_name, '_Category')) {
            return $helper->getCategoryRemoveItemUrl($request, $this);
        }
        if (strstr($class_name, '_Price') || strstr($class_name, '_Decimal')) {
            return $helper->getPriceRemoveItemUrl($request, $this);
        }
        return $helper->getAttributeRemoveItemUrl($request,$this);
    }

    /**
     * @return boolean
     * this function must be called after getUrl as to improve performance (be careful in template)
     * new public function for frontend to see whether the option has been selected
     */
    public function isSelected()
    {
        $selected = false;
        if ($this->getData('selected')) {
            $selected = $this->getData('selected');
        }
        return $selected;
    }
}
