<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gayan
 * Date: 7/24/13
 * Time: 12:36 PM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_Seo_Block_System_Config_Backend_Update extends Mage_Adminhtml_Block_System_Config_Form_Field{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $storeCode = Mage::app()->getRequest()->getParam('store');
        $store = Mage::getModel("core/store")->load($storeCode);
        $storeId = $store->getId();

        $this->setElement($element);
        if (Mage::getStoreConfig('nswebredirects/settings/upload',$storeId))
        {

            $url = Mage::helper('adminhtml')->getUrl('nsredirects/index/index',
                array('store' => Mage::app()->getRequest()->getParam('store')
                ));

            $html = "<script type='text/javascript'>
    //<![CDATA[
    function check() {
        new Ajax.Request('$url', {
            method:     'get',
            onSuccess: function(transport){

            if (transport.responseText){
                alert(transport.responseText);

            }
            }
        });
    }
    //]]>
</script> <button id='ns_mass_redirect_001' type='button' class='scalable' onclick='check()' style=''><span>RUN SCRIPT</span></button>";
        }
        else
        {
            $html = "No CSV file provided.";
        }
        return $html;
    }

}