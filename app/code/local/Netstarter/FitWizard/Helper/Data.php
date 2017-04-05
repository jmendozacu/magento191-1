<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dilhan
 * Date: 6/4/14
 * Time: 3:56 PM
 * To change this template use File | Settings | File Templates.
 */ 
class Netstarter_FitWizard_Helper_Data extends Mage_Core_Helper_Abstract {

    protected $_combinationCollection = null;
    const MEDIA_FOLDER = 'netstarter_fitwizard';
    const XML_PATH_EMAIL_NETSTARTER_FITWIZARD = 'netstarter_fitwizard/emailconfig/email_template';
    const XML_NETSTARTER_FITWIZARD_LOGIC_FILE = 'netstarter_fitwizard/settins/logic_file';


    public function getCombinations()
    {
        if (is_null($this->_combinationCollection)) {
            $postData = Mage::app()->getRequest()->getPost('data');
            if (empty($postData)) {
                $postData = Mage::getSingleton('core/session')->getData('fitwizard_data');
            }
            if ($postData) {
                $combination = Mage::getModel('fitwizard/combination')->getCollection();

                $stepData = Mage::getConfig()->getNode('global/fieldsets/fitwizard_config_options');
                if ($stepData) {
                    $stepData = $stepData->asArray();
                    foreach ($stepData as $id => $data) {
                        $combination->addFieldToFilter($id, $postData[$id]);
                    }
                    $combination->addFieldToFilter('status', 'active');
                }
                $this->_combinationCollection = $combination;

                Mage::getSingleton('core/session')->setData('fitwizard_data', $postData);
            }
        }
        return $this->_combinationCollection;
    }


    public function sendFitwizardEmail($to, $data=array(), $templateConfigPath = self::XML_PATH_EMAIL_NETSTARTER_FITWIZARD)
    {
        if (! $to) return;

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $mailTemplate = Mage::getModel('core/email_template');
        /* @var $mailTemplate Mage_Core_Model_Email_Template */

        $template = Mage::getStoreConfig($templateConfigPath, Mage::app()->getStore()->getId());
        $sendTo = array();
        foreach ($to as $recipient)
        {
            if (is_array($recipient))
            {
                $sendTo[] = $recipient;
            }
            else
            {
                $sendTo[] = array(
                    'email' => $recipient,
                    'name' => null,
                );
            }
        }

        try {

            foreach ($sendTo as $recipient) {
                $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>Mage::app()->getStore()->getId()))
                    ->sendTransactional(
                        $template,
                        Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY,Mage::app()->getStore()->getId()),
                        $recipient['email'],
                        $recipient['name'],
                        $data
                    );
            }

            Mage::getSingleton('core/session')->setData('fitwizard_coupon_sent', true);
        } catch (Exception $e) {
            Mage::logException($e);
        }


        $translate->setTranslateInline(true);

        return $this;
    }

    public function getExistingLogicFile()
    {
        $file = Mage::getBaseDir('media').DS.self::MEDIA_FOLDER.DS.Mage::getStoreConfig(self::XML_NETSTARTER_FITWIZARD_LOGIC_FILE, Mage::app()->getStore()->getId());

        if (file_exists($file)) {
            $fileUrl = Mage::getBaseUrl('media').self::MEDIA_FOLDER.DS.Mage::getStoreConfig(self::XML_NETSTARTER_FITWIZARD_LOGIC_FILE, Mage::app()->getStore()->getId());
            return $fileUrl;
        }
    }


    public function saveLogicFileName($name)
    {
        Mage::getConfig()->saveConfig(self::XML_NETSTARTER_FITWIZARD_LOGIC_FILE, $name, 'default', 0);
        Mage::getConfig()->reinit();
    }


    public function isBraFinderCoupon($coupon)
    {
        $valid = false;
        $prefix = Mage::getStoreConfig(Netstarter_FitWizard_Block_SearchResult::XML_PATH_EMAIL_NETSTARTER_FITWIZARD_COUPON, Mage::app()->getStore()->getId());

        if (!empty($prefix) && strpos($coupon, $prefix) !== false) {
            $valid = true;
        }
        return $valid;
    }

    public function checkBraFinderCouponValidation($code)
    {
        $valid = false;

        $coupon = Mage::getModel('salesrule/coupon')->loadByCode($code, 'code');
        if ($coupon) {
            $expirationDate = $coupon->getExpirationDate();
            $now = Mage::getSingleton('core/date')->gmtTimestamp();

            if (strtotime($expirationDate) > $now) {
                $valid = true;
            }
        }
        return $valid;
    }



    /**
     * for listing pages, on mouse over event, shows a second image... to get that,..
     */
    public function getSecondProductImage($_product)
    {
        $secondImage = '';
        if ($_product instanceof Mage_Catalog_Model_Product) {
            if ($_product->getShowSecondImage() && $_product->getShowSecondImage() !='no_selection') {
                try {
                    $secondImage = Mage::helper('catalog/image')->init($_product, 'show_second_image')->resize(218,260);
                } catch (Exception $e) {

                }
            }
        }

        return $secondImage;
    }


    public function getCategoryImage(Mage_Catalog_Model_Category $category, $type = 'image', $width = 250, $height = 250)
    {
        // return when no image exists
        if (!$image = $category->getData($type)) {
            //if (!$category->getImage()) {
            return false;
        }

        // return when the original image doesn't exist
        $imagePath = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'category'
            . DS . $image;
        if (!file_exists($imagePath)) {
            return false;
        }

        // resize the image if needed
        $rszImagePath = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'category'
            . DS . 'cache' . DS . $width . 'x' . $height . DS
            . $image;
        if (!file_exists($rszImagePath)) {
            $image = new Varien_Image($imagePath);
            $image->resize($width, $height);
            $image->save($rszImagePath);
        }

        // return the image URL
        return Mage::getBaseUrl('media') . '/catalog/category/cache/' . $width . 'x'
        . $height . '/' . $image;
    }

}