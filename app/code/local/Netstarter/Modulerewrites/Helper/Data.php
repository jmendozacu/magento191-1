<?php
/**
 * Created by Netstarter Pty Ltd.
 * User: Dilhan Maduranga
 * Date: 6/7/13
 * Time: 10:25 AM
 */
class Netstarter_Modulerewrites_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function prepareAttribute($attr, $count=0, $start=0)
    {
        $toShow = '';

        if ($attr) {
            $toShow = implode(($count)?array_slice($attr, $start, $count):$attr, ' / ');
        }
        return $toShow;
    }

    public function abbreviateText($string, $limit, $break = ' ', $pad = '...')
    {
        if (strlen(trim($string)) <= $limit){
            return $string;
        }
        $abbreviated = substr($string, 0, strrpos(substr($string, 0, $limit), $break));
        return $abbreviated.$pad;
    }

    /**
     * Option list shown in the Contact Us page => types of inquiry
     * @return array
     */
    public function getContactInquiryTypeList()
    {
        $inquiryTypesStr = Mage::getStoreConfig('contacts/form/inquiry_types');

        try {
            $inquiryTypes = unserialize($inquiryTypesStr);
            if ($inquiryTypes && is_array($inquiryTypes)) {
                return $inquiryTypes;
            }

        } catch (Exception $e) {
            Mage::logException($e);
            //Wrong Serialization
        }

        return array(
            'Temporary Unavailable' => $this->__('Temporary Unavailable'),
            'Other' => $this->__('Other'),
        );
    }

    public function isStoreDateInInterval($specialPriceFrom, $specialPriceTo, $store='')
    {
        if ($specialPriceFrom && $specialPriceTo) {
           return  Mage::app()->getLocale()->isStoreDateInInterval($store, $specialPriceFrom, $specialPriceTo);
        }
    }



    public function getCustomDesignCategory(){
        $customDesignCategoryId =  Mage::getStoreConfig('design/showinlayerd/custom');

        if(($customDesignCategoryId)) {
            $customDesignCategory = Mage::getModel('catalog/category')->load($customDesignCategoryId);
            if($customDesignCategory) {
                return $customDesignCategory;
            }
        }
    }


    public function getWestFieldPixelString($order) {
        // Build the Westfield affiliate (PHG) pixel string for inclusion in the page source (Ben Tubby September 2013)
        // Get the order id, Set the order model, Load the order with the successful order id

        // Get the order grand total NOTE this should be total order - shipping (we don't charge commission on the shipping amount)
        // $grandTotal = $order->getGrandTotal() - $order->getShippingAmount(); //Not used

        // Get any coupon codes $order->getCouponCode();
        // Get any gift certificate codes $order->getGiftcertCode();

        // For the standard pixel, we need item specific information
        $initialPixelString = '';
        if ($order && $order instanceof Mage_Sales_Model_Order) {
            $pixelItemsArray= "";
            $items = $order->getItemsCollection('',true);

            foreach ($items as $item) {

                // Get a category for each product in the order
                $productId = $item->getProductId();
                $product = Mage::getModel('catalog/product')->load($productId);
                $categoryIds = $product->getCategoryIds();

                //Excluding Categories
                $excludeCategories = Mage::getStoreConfig('excludes_section/excludes_tab/categories', Mage::app()->getStore());
                $westFieldCampaignCode = '10l123';
                $excludeCategories = is_array($excludeCategories) ? $excludeCategories : array();
                $itemCategory = "";

                foreach ($categoryIds as $category_id) {
                    if (!in_array($category_id, $excludeCategories)) {  //check excluding categories
                        $_cat = Mage::getModel('catalog/category')->load($category_id);
                        $_catLevel = $_cat->getLevel(); //Check the main category
                        if ($_catLevel == '2') {
                            $itemCategory = $_cat->getName();
                            break;
                        } else if ($_catLevel == '3') {
                            $_parentCat = $_cat->getParentCategory();
                            $_parentCatId = $_parentCat->getId();
                            if (!in_array($_parentCatId, $excludeCategories)) {
                                $itemCategory = $_parentCat->getName();
                                break;
                            }
                        }
                    }
                }

                // Get the item price, minus any discounts
                $itemPrice = number_format($item->getPriceInclTax(), 2, '.',''); //Added per request

                $qtyInvoiced = $item->getQtyToInvoice();
                $qtyOrdered = $item->getQtyOrdered();

                $qty = '';
                if(!empty($qtyInvoiced)) $qty = $qtyInvoiced;
                elseif(!empty($qtyOrdered)) $qty = $qtyOrdered;

                // Build the item array string
                $pixelItemsArray .= "["
                    ."category:"  .$itemCategory
                    ."/sku:"      .$item->getSku()
                    ."/value:"    .$itemPrice
                    ."/quantity:" .$qty // $item->getQtyOrdered() item->getQtyToShip()
                    ."]";
            }
            $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
            $currencyCode = $currencyCode ? $currencyCode : 'AUD';
            $initialPixelString = "https://prf.hn/conversion/campaign:".$westFieldCampaignCode."/conversionref:". $this->escapeHtml($order->getIncrementId()).'/currency:'.$currencyCode.'/'.$this->escapeHtml($pixelItemsArray);

        }
        return $initialPixelString;
        //public function getFormattedCountryList
    }

    public function isModuleEnabled($module='')
    {
        switch($module) {
            case 'newsletter':
                $module = 'Mage_Newsletter';
                break;
            default:
                break;
        }
        return Mage::helper('core')->isModuleOutputEnabled($module);
    }

    public function getGridSpotlightPosition($position = 6)
    {
        //take grid spotlight config value and return: default 7: then should return 6
        $customDesignCategoryId =  Mage::getStoreConfig('design/catalogpage/spotlightpos');
        return $customDesignCategoryId ? $customDesignCategoryId - 1: $position;
    }

    public function getLoggedInUserEmail() {
        $userEmail = null;
        if (Mage::getSingleton('customer/session')->isLoggedIn()){
            $userEmail = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        }
        return $userEmail;// To get Email Id of a customer
    }

    /**
     * Set SLI Cart Info Cookie - SLICART
     * So that SLI search page can be enhanced with mini cart
     * @param $cartItemsConfig Cart Information as an array
     */
    public function setSliCartContentCookie ($cartItemsConfig) {
        try{
            $domain = $_SERVER['HTTP_HOST'];
            $domain = str_replace("www.",'',$domain);
            setcookie('SLICART', Mage::helper('core')->jsonEncode($cartItemsConfig), 0, '/', $domain);
        } catch (Exception $e) {
            Mage::logException($e);
        }

    }
}