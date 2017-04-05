<?php
class Netstarter_Modulerewrites_Model_Customer_Observer
{
    public function reassignOrder($observer)
    {
        $postData = Mage::app()->getRequest()->getPost();
        $event = $observer->getEvent();
        $customer = $event->getCustomer();

        try{

            if(!empty($postData['diredro'])){

                $checksum = sha1($customer->getEmail().$postData['diredro']);

                if($checksum && $postData['code']){

                    $resource = Mage::getSingleton('core/resource');
                    $write  =  $resource->getConnection('core_write');
                    $write->update($resource->getTableName('sales/order'), array('customer_id' => $customer->getEntityId()),
                        "entity_id = {$postData['diredro']}");
                }
            }
        }catch (Exception $e){
            Mage::logException($e);
        }
    }
}