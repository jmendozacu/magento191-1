<?php

/**
 * Overridden as we detected a path issue
 * Class Netstarter_Modulerewrites_Model_ImportExport_Scheduled_Operation
 */
class Netstarter_Modulerewrites_Model_ImportExport_Scheduled_Operation extends Enterprise_ImportExport_Model_Scheduled_Operation
{

    /**
     * Overridden as we detected a path issue
     *
     * Get file path of history operation files
     *
     * @throws Mage_Core_Exception
     * @return string
     */
    public function getHistoryFilePath()
    {
        $dirPath = basename(Mage::getBaseDir('var')) . DS . Mage_ImportExport_Model_Abstract::LOG_DIRECTORY
            . date('Y' . DS . 'm' . DS . 'd') . DS . self::FILE_HISTORY_DIRECTORY . DS;
        if (!is_dir(Mage::getBaseDir() . DS . $dirPath)) {
            mkdir(Mage::getBaseDir() . DS . $dirPath, 0750, true);
        }

        $fileName = $fileName = join('_', array(
            Mage::getModel('core/date')->date('H-i-s'),
            $this->getOperationType(),
            $this->getEntityType()
        ));
        $fileInfo = $this->getFileInfo();
        if (isset($fileInfo['file_format'])) {
            $extension = $fileInfo['file_format'];
        } elseif(isset($fileInfo['file_name'])) {
            $extension = pathinfo($fileInfo['file_name'], PATHINFO_EXTENSION);
        } else {
            Mage::throwException(Mage::helper('enterprise_importexport')->__('Unknown file format'));
        }

        return Mage::getBaseDir() . DS . $dirPath . $fileName . '.' . $extension;
    }
}
