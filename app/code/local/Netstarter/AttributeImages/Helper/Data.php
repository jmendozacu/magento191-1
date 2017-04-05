<?php
/**
 * @author Prasad
 *
 * Class Netstarter_Location_Helper_Data
 */
class Netstarter_AttributeImages_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_mediaFolder = 'attributeimages';
    protected $_cdnNumber = NULL;

    /**
     * Prepare the image name for the attribute option
     * @param $attribute
     * @param $optionId
     * @param string $extension
     * @return string
     */
    public function getOptionImageName($attribute, $optionId, $extension='.png')
    {
        $imageName = $attribute.'_'.$optionId.$extension;
        return $imageName;
    }

    /**
     * @param $code
     * @param $data
     * @param string $extension
     * @return array
     */
    public function getImageDataArray($code, $data, $limit=1, $extension=".png")
    {
        $imageData = array();
        if ($data && is_array($data)) {
            foreach ($data as $id) {
                $mediaPath = Mage::getBaseUrl('media')  . $this->_mediaFolder . DS .$code. DS;
                $dirPath = Mage::getBaseDir('media')  . DS . $this->_mediaFolder . DS .$code. DS;
                $filePath = $mediaPath.$this->getOptionImageName($code, $id, $extension).'?rev='.$this->getCdnNumber();
                $dirPath = $dirPath.$this->getOptionImageName($code, $id, $extension);

                if ($this->fileExists($dirPath)) {
                    $imageData[$id] = $filePath;
                }
            }
        }
        return $imageData;
    }

    /**
     * @param $file
     * @return bool
     */
    public function fileExists($file)
    {
        return ($file && file_exists($file)) ? true : false;
    }

    public function getCdnNumber () {
        if (!$this->_cdnNumber) {
            $this->_cdnNumber = Mage::getStoreConfig('cdn/settings/rev_number_attrimg');
        }
        return $this->_cdnNumber;
    }

    public function increaseImageCdnNumber($new=null) {
        $cdnRevision = $this->getCdnNumber();
        $cdnRevision += empty($new) ? 1 : $new;
        Mage::getConfig()->saveConfig('cdn/settings/rev_number_attrimg', $cdnRevision, 'default', 0);
        Mage::getConfig()->reinit();
    }
}