<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gayan
 * Date: 7/17/13
 * Time: 8:58 AM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_Seo_Block_Globaltags extends  Mage_Core_Block_Template{

    /*add global head js scripts for seo purposes*/
    public function addHeadJs(){
        $headjs = Mage::getStoreConfig('globaltags/settings/embedded_head_tag');
        if($headjs && $headjs !='' ){
            //return "<script>".$headjs."</script>";
            return $headjs;
        }
        return;
    }

    /*add global body js scripts for seo purposes*/
    public function addBodyJs(){
        $bodyjs = Mage::getStoreConfig('globaltags/settings/embedded_body_tag');
        if($bodyjs && $bodyjs !='' ){
            //return "<script>".$bodyjs."</script>";
            return $bodyjs;
        }
        return;
    }
}