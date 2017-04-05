<?php
/**
 * Created by JetBrains PhpStorm.
 * User: suresh
 * Date: 3/26/13
 * Time: 3:37 PM
 * To change this template use File | Settings | File Templates.
 */
/**
 * Class Netstarter_Cdn_Model_Package
 * To add the query string at the end of css and js urls
 */
class Netstarter_Cdn_Model_Package extends Mage_Core_Model_Design_Package
{
    /**
     * @param null $file
     * @param array $params
     * @return string
     */
    public function getSkinUrl($file = null, array $params = array())
    {
        Varien_Profiler::start(__METHOD__);
        if (empty($params['_type'])) {
            $params['_type'] = 'skin';
        }
        if (empty($params['_default'])) {
            $params['_default'] = false;
        }
        $this->updateParamDefaults($params);
        if (!empty($file)) {
            $result = $this->_fallback(
                $file,
                $params,
                $this->_fallback->getFallbackScheme(
                    $params['_area'],
                    $params['_package'],
                    $params['_theme']
                )
            );
        }
        $versionNumber = Mage::helper('cdn')->getVersionNumber();
        $result = $this->getSkinBaseUrl($params) . (empty($file) ? '' : $file);
        if ($versionNumber) {
            $newName = $file . '?v=' . $versionNumber;
            $result = $this->getSkinBaseUrl($params) . (empty($newName) ? '' : $newName);
        }

        Varien_Profiler::stop(__METHOD__);
        return $result;
    }

    public function getMergedCssUrl($files)
    {
        // secure or unsecure
        $isSecure = Mage::app()->getRequest()->isSecure();
        $mergerDir = $isSecure ? 'css_secure' : 'css';
        $targetDir = $this->_initMergerDir($mergerDir);
        if (!$targetDir) {
            return '';
        }

        // base hostname & port
        $baseMediaUrl = Mage::getBaseUrl('media', $isSecure);
        $hostname = parse_url($baseMediaUrl, PHP_URL_HOST);
        $port = parse_url($baseMediaUrl, PHP_URL_PORT);
        if (false === $port) {
            $port = $isSecure ? 443 : 80;
        }

        // merge into target file
        $targetFilename = md5(implode(',', $files) . "|{$hostname}|{$port}") . '.css';
        $mergeFilesResult = $this->_mergeFiles(
            $files, $targetDir . DS . $targetFilename,
            false,
            array($this, 'beforeMergeCss'),
            'css'
        );
        if ($mergeFilesResult) {
            $versionNumber = Mage::helper('cdn')->getVersionNumber();
            if ($versionNumber) {
                return $baseMediaUrl . $mergerDir . '/' . $targetFilename. '?v=' . $versionNumber;
            }
            return $baseMediaUrl . $mergerDir . '/' . $targetFilename;
        }
        return '';
    }


    public function getMergedJsUrl($files)
    {
        $targetFilename = md5(implode(',', $files)) . '.js';
        $targetDir = $this->_initMergerDir('js');
        if (!$targetDir) {
            return '';
        }
        if ($this->_mergeFiles($files, $targetDir . DS . $targetFilename, false, null, 'js')) {
            $versionNumber = Mage::helper('cdn')->getVersionNumber();
            if ($versionNumber) {
                return Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure()) . 'js/' . $targetFilename. '?v=' . $versionNumber;
            }
            return Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure()) . 'js/' . $targetFilename;
        }
        return '';
    }
}
