<?php

class TV_WebFormDownload_IndexController extends Mage_Core_Controller_Front_Action {

    public function IndexAction() {

        $this->loadLayout();
        $this->getLayout()->getBlock("head")->setTitle($this->__("Titlename"));
        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
        $breadcrumbs->addCrumb("home", array(
            "label" => $this->__("Home Page"),
            "title" => $this->__("Home Page"),
            "link" => Mage::getBaseUrl()
        ));

        $breadcrumbs->addCrumb("titlename", array(
            "label" => $this->__("Titlename"),
            "title" => $this->__("Titlename")
        ));

        $this->renderLayout();
    }

    function downloadfileAction() {

        $path = Mage::getBaseDir('media') . '/downloads/';
        $files = scandir($path);
        for ($i = 0; $i < count($files); $i++) {
            if ($files[$i] != '.' || $files[$i] != '..') {
                if (file_exists($path . $file[$i])) {
                    $filename = $files[$i];
                    header("Content-Description: File Transfer");
                    header("Content-Type: application/octet-stream");
                    header("Content-Disposition: attachment; filename=\"$filename\"");
                    readfile($filename);
                }
            }
        }
        exit();
    }

}
