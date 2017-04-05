<?php
class ARW_Ajaxcart_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function jsParam($obj)
   {
       $param = array(
           'send_url'           =>  $obj->getSendUrl(),
           'update_url'         =>  $obj->getUpdateUrl(),
           'src_image_progress' =>  $obj->getSkinUrl('images/loading.gif'),
           'error'              =>  $this->__(' ↑ This is a required field.'),
           'is_product_view'    =>  Mage::registry('current_product') ? 1 : 0
       );
       if(Mage::registry('current_product'))
              $param['product_id'] = Mage::registry('current_product')->getId();
      
       return Zend_Json::encode($param);
   }
}
