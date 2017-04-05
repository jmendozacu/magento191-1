<?php
/**
 * Class Netstarter_Productwidget_Model_Adminhtml_Observer
 */
class Netstarter_Productwidget_Model_Adminhtml_Observer
{

    /**
     * Build the Complete the looks product association
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveLookBookProducts(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        if($product->hasData('looks')){

            $lookStr = $product->getData('looks');
            $decodedLooks = Mage::helper('adminhtml/js')->decodeGridSerializedInput($lookStr);

            $lookprocollection = Mage::getResourceModel('productwidget/look_collection')
                                ->addFieldToFilter('product_id',$product->getId());

            if ($lookprocollection->count()) {

                foreach($lookprocollection as $look){
                    $look->delete();
                }
            }

            if (!empty($decodedLooks)) {
                foreach($decodedLooks as $productId => $look){

                    $link = Mage::getModel('productwidget/look');
                    $link->setProductId($product->getId());
                    $link->setLinkedProductId($productId);
                    if (!empty($look['position'])) {
                        $link->setPosition($look['position']);
                    }

                    $link->save();
                }
            }

//            if(!empty($looks)){
//
//                foreach($looks as $look){
//
//                    $proId = explode('=', $look);
//
//                    if(!empty($proId[0])){
//
//                        $link = Mage::getModel('productwidget/look');
//                        $link->setProductId($product->getId());
//                        $link->setLinkedProductId($proId[0]);
//
//                        $link->save();
//                    }
//                }
//            }
        }

        return $this;
    }
}