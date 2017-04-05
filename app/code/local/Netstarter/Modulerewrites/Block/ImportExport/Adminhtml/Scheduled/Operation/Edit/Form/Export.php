<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 2/12/14
 * Time: 3:09 PM
 * To change this template use File | Settings | File Templates.
 */ 
class Netstarter_Modulerewrites_Block_ImportExport_Adminhtml_Scheduled_Operation_Edit_Form_Export extends Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Edit_Form_Export
{

    /**
     * Add file information fieldset to form
     *
     * @param Varien_Data_Form $form
     * @param Enterprise_ImportExport_Model_Scheduled_Operation $operation
     * @return Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Edit_Form
     */
    protected function _addFileSettings($form, $operation)
    {
        $fieldset = $form->addFieldset('file_settings', array(
            'legend' => $this->getFileSettingsLabel()
        ));


        $fieldset->addField('server_type', 'select', array(
            'name'      => 'file_info[server_type]',
            'title'     => Mage::helper('enterprise_importexport')->__('Server Type'),
            'label'     => Mage::helper('enterprise_importexport')->__('Server Type'),
            'required'  => true,
            'values'    => Mage::getSingleton('enterprise_importexport/scheduled_operation_data')
                ->getServerTypesOptionArray(),
        ));

        $fieldset->addField('file_path', 'text', array(
            'name'      => 'file_info[file_path]',
            'title'     => Mage::helper('enterprise_importexport')->__('File Directory'),
            'label'     => Mage::helper('enterprise_importexport')->__('File Directory'),
            'required'  => true,
            'note'      => Mage::helper('enterprise_importexport')->__('For Type "Local Server" use relative path to Magento installation, e.g. var/export, var/import, var/export/some/dir')
        ));

        $fieldset->addField('host', 'text', array(
            'name'      => 'file_info[host]',
            'title'     => Mage::helper('enterprise_importexport')->__('FTP Host[:Port]'),
            'label'     => Mage::helper('enterprise_importexport')->__('FTP Host[:Port]'),
            'class'     => 'ftp-server sftp-server server-dependent'
        ));

        $fieldset->addField('username', 'text', array(
            'name'      => 'file_info[username]',
            'title'     => Mage::helper('enterprise_importexport')->__('User Name'),
            'label'     => Mage::helper('enterprise_importexport')->__('User Name'),
            'class'     => 'sftp-server server-dependent'
        ));

        $fieldset->addField('user', 'text', array(
            'name'      => 'file_info[user]',
            'title'     => Mage::helper('enterprise_importexport')->__('User Name'),
            'label'     => Mage::helper('enterprise_importexport')->__('User Name'),
            'class'     => 'ftp-server server-dependent'
        ));

        $fieldset->addField('password', 'password', array(
            'name'      => 'file_info[password]',
            'title'     => Mage::helper('enterprise_importexport')->__('Password'),
            'label'     => Mage::helper('enterprise_importexport')->__('Password'),
            'class'     => 'ftp-server sftp-server server-dependent'
        ));

        $fieldset->addField('file_mode', 'select', array(
            'name'      => 'file_info[file_mode]',
            'title'     => Mage::helper('enterprise_importexport')->__('File Mode'),
            'label'     => Mage::helper('enterprise_importexport')->__('File Mode'),
            'values'    => Mage::getSingleton('enterprise_importexport/scheduled_operation_data')
                ->getFileModesOptionArray(),
            'class'     => 'ftp-server sftp-server server-dependent'
        ));

        $fieldset->addField('passive', 'select', array(
            'name'      => 'file_info[passive]',
            'title'     => Mage::helper('enterprise_importexport')->__('Passive Mode'),
            'label'     => Mage::helper('enterprise_importexport')->__('Passive Mode'),
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'class'     => 'ftp-server sftp-server server-dependent'
        ));

        return $this;
    }

}