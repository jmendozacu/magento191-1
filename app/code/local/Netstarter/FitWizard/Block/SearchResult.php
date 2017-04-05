<?php
/**
 * Created by JetBrains PhpStorm.
 * @author  http://www.netstarter.com.au
 *
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_FitWizard_Block_SearchResult extends Mage_Core_Block_Template
{
    //netstarter_fitwizard_emailconfig_email_template
    const XML_PATH_EMAIL_NETSTARTER_FITWIZARD_COUPON = 'netstarter_fitwizard/settings/coupon';
    const XML_PATH_EMAIL_NETSTARTER_RULE_ID = 'netstarter_fitwizard/settings/rule_id';
    const XML_PATH_EMAIL_NETSTARTER_CODE_LENGTH = 'netstarter_fitwizard/settings/code_length';
    const XML_PATH_EMAIL_NETSTARTER_VALID_DAYS = 'netstarter_fitwizard/settings/valid_days';
    const XML_PATH_EMAIL_NETSTARTER_STRICT_COUPONS = 'netstarter_fitwizard/settings/strict_coupons';

    protected $_collection = null;
    protected $_optionMapping = array(
        'cup_preference'
    );

    protected $_pageSize = 5;

    public function __construct()
    {
        parent::__construct();
        $collection = $this->_getProductCollection(); //Mage::getModel('catalog/product')->getCollection();
        $this->setCollection($collection);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!is_null($collection = $this->getCollection())) {
            $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
            $pager->setTemplate('fitwizard/pager.phtml');
            $pager->setAvailableLimit(array($this->_pageSize=>$this->_pageSize,'all'=>'all'));
            $pager->setCollection($collection);
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }

        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }


    public function _afterToHtml($html)
    {
        //if (!is_null($this->getCollection()) && $this->getCollection()->getSize()) {
        //    $this->sendEmailNotificationEmail();
        //}
        return parent::_afterToHtml($html);
    }



    public function sendEmailNotificationEmail()
    {
        $data = Mage::getSingleton('core/session')->getData('fitwizard_data');
        if (!empty($data['email'])) {

            $alreadySent = Mage::getSingleton('core/session')->getData('fitwizard_coupon_sent');
            if (!$alreadySent) {
                $emailData = array();


                //If strict Coupon email is turned on at backend
                if ($this->getStoreConfig(self::XML_PATH_EMAIL_NETSTARTER_STRICT_COUPONS) == true) {
                    $couponEmail = Mage::getSingleton('fitwizard/couponEmail');
                    if (!$couponEmail->couponEmailExists($data['email'])) {

                        $couponCode = $this->getCouponCode();
                        $emailData['coupon_code'] = $couponCode;
                        //Save Email and Coupon Code in a table so one email gets coupon for one time
                        //$couponEmail->saveCouponEmail($data['email'], $couponCode);

                        $sent = $this->helper('fitwizard')->sendFitwizardEmail(array($data['email']), $emailData);
                        if ($sent) {
                            $couponEmail->saveCouponEmail($data['email'], $couponCode);
                        }
                    }
                } else {
                    $couponCode = $this->getCouponCode();
                    $emailData['coupon_code'] = $couponCode;
                    $this->helper('fitwizard')->sendFitwizardEmail(array($data['email']), $emailData);
                }
            }
        }
    }

    public function getExpireDateString()
    {
        $validDays = $this->getStoreConfig(self::XML_PATH_EMAIL_NETSTARTER_VALID_DAYS);
        if ($validDays) {
            return '+'.$validDays.' day';
        }
        return false;
    }

    public function getCouponCode()
    {
        try {
            $generator = Mage::getModel('salesrule/coupon_massgenerator');

            $coupon = Mage::getModel('salesrule/coupon');

            $endingDate = new DateTime();
            if ($expire = $this->getExpireDateString()) {
                $endingDate->modify($expire);
            }


            /**
             * Possible format values include:
             * Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC
             * Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHABETICAL
             * Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_NUMERIC
             */

            $data = array(
                'max_probability'   => .25,
                'max_attempts'      => 10,
                'uses_per_customer' => 1,
                'uses_per_coupon'   => 1,
                'qty'               => 1, //number of coupons to generate
                'length'            => $this->getStoreConfig(self::XML_PATH_EMAIL_NETSTARTER_CODE_LENGTH), //length of coupon string
                'to_date'           => $endingDate->format("Y-m-d"), //ending date of generated promo
                'format'            => Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC,
                'rule_id'           => $this->getStoreConfig(self::XML_PATH_EMAIL_NETSTARTER_RULE_ID),  //the id of the rule you will use as a template,
                'prefix'            => $this->getStoreConfig(self::XML_PATH_EMAIL_NETSTARTER_FITWIZARD_COUPON)
            );

            $generator->validateData($data);
            $generator->setData($data);
            $code = $generator->generateCode();

            $coupon->setId(null)
                ->setRuleId($this->getStoreConfig(self::XML_PATH_EMAIL_NETSTARTER_RULE_ID))
                ->setUsageLimit(1)
                ->setUsagePerCustomer(1)
                ->setExpirationDate($endingDate->format("Y-m-d"))
                ->setCreatedAt( Mage::getSingleton('core/date')->gmtTimestamp() )
                ->setType(Mage_SalesRule_Helper_Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED)
                ->setCode($code)
                ->save();

            return $code;
        } catch (Exception $e ){

        }

        return Mage::getStoreConfig(self::XML_PATH_EMAIL_NETSTARTER_FITWIZARD_COUPON, Mage::app()->getStore()->getId());
    }


    protected function _getProductCollection()
    {
        $collection = null;
        if (is_null($this->_collection)) {
            $combination = $this->helper('fitwizard')->getCombinations();

            if (!is_null($combination) && $combination->getSize()) {
                $ctf = array();

                //$coreResource = Mage::getSingleton('core/resource');
                foreach ($combination->getItems() as $combination) {
                    $ctf[] = $combination->getCategoryId();
                }

                $collection= Mage::getModel('catalog/product')->getCollection();
                $collection->distinct('e.entity_id');
                $collection->getSelect()->columns('e.*');
                $collection->getSelect()->join(
                    array('cat_index_sub'=> 'catalog_category_product_index'), 'cat_index_sub.product_id = e.entity_id and cat_index_sub.category_id IN ('.implode(',' , $ctf).')', array('cat_index_sub.position')
                );

                $postData = Mage::getSingleton('core/session')->getData('fitwizard_data');

                if (!empty($postData['size'])) {
                    $eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
                    $sizeCode = $eavAttribute->getIdByCode('catalog_product', 'size');

                    $collection->getSelect()->join(
                        array('size_idx'=> 'catalog_product_index_eav'), 'size_idx.entity_id = e.entity_id and size_idx.attribute_id = '.$sizeCode.' AND size_idx.store_id = 1 AND size_idx.value IN ('.$postData['size'].')', array('size_idx.value')
                    );
                }

                //Mage::log($collection->getSelect()->assemble());
            }
        }
        return $collection;
    }


    public function getRecommendedCategories()
    {
        $combinations = $this->helper('fitwizard')->getCombinations();
        $categories = null;
        $recommendedCategoryIds = array();
        if (!is_null($combinations)) {
            foreach ($combinations as $combination) {
                $recommendedCategoryIds[] = $combination->getCategoryId();
            }
        }

        if ($recommendedCategoryIds) {
            $categories = Mage::getModel('catalog/category')->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $recommendedCategoryIds ))
                ->addAttributeToSelect(array('name', 'url_key', 'url_path', 'image', 'thumbnail'));
        }

        return $categories;
    }

    public function getStoreConfig($config = '')
    {
        if (!empty($config)) {
            return Mage::getStoreConfig($config, Mage::app()->getStore()->getId());
        }
    }
}