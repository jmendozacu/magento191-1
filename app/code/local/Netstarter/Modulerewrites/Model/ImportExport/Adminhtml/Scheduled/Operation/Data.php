<?php
/**
 * Class Netstarter_Modulerewrites_Model_ImportExport_Adminhtml_Scheduled_Operation_Data
 *
 * Added the SFTP export capability
 */
class Netstarter_Modulerewrites_Model_ImportExport_Adminhtml_Scheduled_Operation_Data extends Enterprise_ImportExport_Model_Scheduled_Operation_Data
{
    /**
     * Get server types option array
     *
     * @return array
     */
    public function getServerTypesOptionArray()
    {
        return array(
            'file'  => Mage::helper('enterprise_importexport')->__('Local Server'),
            'ftp'   => Mage::helper('enterprise_importexport')->__('Remote FTP'),
            'sftp'   => Mage::helper('enterprise_importexport')->__('Remote SFTP'),
        );
    }
}
