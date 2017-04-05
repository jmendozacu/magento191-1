<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Netstarter_Menu_Model_Observer
{

    /**
     * handles the additional functionality when the catalog_category_prepare_save event triggers
     * 1. handle brand image upload
     * 2. delete brand image
     *
     * @param $observer
     */
    public function saveAdditionalCategoryData($observer)
    {
        $event = $observer->getEvent();
        $category = $event->getCategory();

        // handling the brand image upload
        $imageUpladed = false;
        if (isset($_FILES['navigation_image']['name']) && $_FILES['navigation_image']['name'] != '') {
            try {
                $imageUpladed = true;
                /* Starting upload */
                $uploader = new Varien_File_Uploader('navigation_image');

                // Any extention would work
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);

                $path = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'category' . DS;
                $result = $uploader->save($path);
                $category->setNavigationImage($result['file']);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        // deleting image from the server and the db
        if (isset($_POST['general']['navigation_image']['delete']) && $_POST['general']['navigation_image']['delete'] == 1) {
            $fileName = $_POST['general']['navigation_image']['value'];
            $path = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'category' . DS;
            if (unlink($path . $fileName) && !$imageUpladed)
                $category->setNavigationImage(null);
        }
    }

}