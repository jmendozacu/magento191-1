<?php
/**
 * Created by JetBrains PhpStorm.
 * @author  http://www.netstarter.com.au
 *
 * To change this template use File | Settings | File Templates
 */
class Netstarter_FitWizard_Block_Wizard extends Mage_Core_Block_Template
{
    const XML_PATH_SETTINGS_SIZE_FILTER = 'netstarter_fitwizard/settings/size_filter';

    protected $_staticPrefix = 'bra-finder-wizard-';
    protected $_staticSteps = array('size', 'getemail', 'success');
    public $_activeStep = '';
    protected $_stepData;
    protected $_steps = array(
        'size' => 'Size',
        'separation' => 'Separation',
        'fullness' => 'Fullness',
        'position' => 'Position',
        'results' => 'Results',
    );

    /**
     * setter
     * @param $step
     */
    public function setActiveStep($step)
    {
        $this->_activeStep = $step;
    }

    /**
     * getter
     * @return string
     */
    public function getActiveStep()
    {
        return $this->_activeStep;
    }

    /**
     * @param string $step
     * @return string
     */
    public function getStepStaticBlock($step='size')
    {
        $blockHtml = '';
        if ($this->_staticSteps && in_array($step, $this->_staticSteps)) {
            $blockId = $this->_staticPrefix. $step;
            $cmsBlock = Mage::getModel('cms/block')->load($blockId);
            if ($cmsBlock->getIsActive()) {
                $blockHtml = $cmsBlock->getContent();
            }
        }
        return $blockHtml;
    }

    public function getWizardSteps()
    {
        return $this->_steps;
    }


    public function getSizeList()
    {
        $options = array();
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'size');
        if ($attribute->usesSource()) {
            $options = $attribute->getSource()->getAllOptions(false);
            $options = $this->filterSizeList($options);
        }
        return $options;
    }

    public function filterSizeList($sizes = array())
    {
        $filteredSizes = array();
        $sizeFilterList = Mage::getStoreConfig(Netstarter_FitWizard_Block_Wizard::XML_PATH_SETTINGS_SIZE_FILTER, Mage::app()->getStore()->getId());

        if ($sizeFilterList) {
            $sizeList = explode(',' , $sizeFilterList);
            $sizeList = array_map('trim', $sizeList);
            foreach ($sizes as $size) {
                if (!empty($size['label']) && in_array($size['label'], $sizeList)) {
                    $filteredSizes[] = $size;
                }
            }
        }

        return $filteredSizes;
    }

    public function getRequestData($data='', $secondLevel='')
    {
        $output = '';
        if (!$this->_stepData) {
            $this->_stepData = Mage::getSingleton('core/session')->getSearchWizardData();
        }
        $wizardData = $this->_stepData;

        if (!$data) {
            $output = $wizardData;
        } elseif ($data && !$secondLevel) {
            $output = !empty($wizardData[$data]) ? $wizardData[$data] : null;
        } elseif ($data && $secondLevel) {
            $output = !empty($wizardData[$data][$secondLevel]) ? $wizardData[$data][$secondLevel] : false;
        }
        return $output;
    }

    public function getSelectedSize()
    {
        return $this->getRequestData('size_label');
    }

    public function getStepData() {
        if (empty($this->_stepData)) {
            return $this->getRequestData();
        } else {
            return array();
        }
        //return !empty($this->_stepData) ? $this->_stepData : array();
    }

    public function getWizardStepData() {
        $stepData = Mage::getConfig()->getNode('global/fieldsets/fitwizard_config_options');
        if ($stepData) {
            return (array) $stepData;
        }
    }

    public function getOptionImgPath()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'wysiwyg/netstarter_fitwizard/';
    }
}