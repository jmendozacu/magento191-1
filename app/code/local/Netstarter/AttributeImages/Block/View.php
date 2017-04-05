<?php
/**
 * Created by JetBrains PhpStorm.
 * User: http://www.netstarter.com.au
 * Date: 3/7/14
 * Time: 7:58 AM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_AttributeImages_Block_View extends Mage_Catalog_Block_Product_View
{
    protected $_attributeValueLimit = 1;
    protected $_attributeCode = '';
    /**
     * @param int $limit
     */
    public function setAttributeValueLimit($limit=1)
    {
        $this->_attributeValueLimit = $limit;
    }

    /**
     * @return int
     */
    public function getAttributeValueLimit()
    {
        return $this->_attributeValueLimit;
    }

    /**
     * @param $code
     */
    public function setAttributeCode($code) {
        $this->_attributeCode = $code;
    }

    public function getAttributeCode() {
        return $this->_attributeCode;
    }

    /**
     * get attribute images to the prioritized attribute values
     * @return mixed
     */
    public function getAttributeImages()
    {
        $product  = $this->getProduct();
        $imageData = array();
        $a =$this->getAttributeCode();
        $topOption = array();
        $options = $product->getResource()
            ->getAttribute($a)
            ->getSource()
            ->getAllOptions(false);

        $productData = $this->getProduct()->getData($this->getAttributeCode());
        if ($productData && $options) {
            $productData = explode(',' , $productData);

            foreach ($options as $option) {
                if (!empty($option) && in_array($option['value'], $productData)) {
                    $topOption[] = $option['value'];
                    break;
                }
            }
            $imageData = $this->helper('attributeimages')->getImageDataArray($this->getAttributeCode(), $topOption);
        }
        return $imageData;
    }

}