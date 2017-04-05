<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gayan
 * Date: 9/24/13
 * Time: 11:04 AM
 * To change this template use File | Settings | File Templates.
 */

class Netstarter_Seo_Block_Tagmanager extends Mage_Core_Block_Text{

    const XML_PATH_ACTIVE  = 'tagmanager/settings/status';
    const XML_PATH_ACCOUNT = 'tagmanager/settings/id';
    const XML_PATH_AFFILIATION = 'tagmanager/settings/affiliation';


    public function isGoogleTagmanagerAvailable($store = null)
    {
        $accountId = Mage::getStoreConfig(self::XML_PATH_ACCOUNT, $store);
        return $accountId && Mage::getStoreConfigFlag(self::XML_PATH_ACTIVE, $store);
    }

    /**
     * Render information about specified orders and their items
     * */

    protected function _TagManagerOrderTrackingCode()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }

        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds))
        ;
        $result = array();

        foreach ($collection as $order) {
            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }
            if ($order->getBaseGrandTotal()>0)
            {
                $transactionTotal=$order->getBaseGrandTotal()-$order->getBaseTaxAmount()-$order->getBaseShippingAmount();
            }
            else
            {
                $transactionTotal=$order->getBaseGrandTotal();
            }

            $result[] = "<script>";
            $result[] = "dataLayer = [{";

            $result[] = "'transactionId' : '".$order->getIncrementId()."',";

            if(Mage::getStoreConfig(self::XML_PATH_AFFILIATION) != ""){
                $result[] = "'transactionAffiliation' : '".Mage::getStoreConfig(self::XML_PATH_AFFILIATION)."',";
            }

            $result[] = "'transactionTotal' : '".
                Mage::getModel('directory/currency')->formatTxt(
                    $transactionTotal,
                    array('display' => Zend_Currency::NO_SYMBOL)
                )."',";
            $result[] = "'transactionTax' : '".
                Mage::getModel('directory/currency')->formatTxt(
                    $order->getBaseTaxAmount(),
                    array('display' => Zend_Currency::NO_SYMBOL)
                )."',";
            $result[] = "'transactionShipping' : '".Mage::getModel('directory/currency')->formatTxt(
                    $order->getBaseShippingAmount(),
                    array('display' => Zend_Currency::NO_SYMBOL)
                )."',";
            $result[] = "'transactionType' : '".$order->getPayment()->getMethodInstance()->getCode()."',";

            $result[] = "'transactionProducts' : [";



            $itm = array();

            foreach ($order->getAllVisibleItems() as $item) {


                $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$item->getSku());

                $catName = array();
                foreach($product->getCategoryIds() as $catId){

                    $_cat = Mage::getModel('catalog/category')->load($catId) ;
                    $catName[] = $_cat->getName();

                    //break; //just one category to list

                }

                $itm[] = "{
                'sku' : '".$this->jsQuoteEscape($item->getSku())."',
                'name' : '".$this->jsQuoteEscape($item->getName())."',
                'category' : '".implode(", ",$catName)."',
                'price' : '".Mage::getModel('directory/currency')->formatTxt(
                        $item->getBasePrice(),
                        array('display' => Zend_Currency::NO_SYMBOL)
                    )."',
                'quantity' : '".intval($item->getQtyOrdered())."',
                }";


            }
            $result[] = implode(",",$itm);
            $result[] = "]}];";
            $result[] = "dataLayer.push();
            </script>";

        }
        return implode("\n", $result);
    }



    protected function _toHtml()
    {
        $controller 	= Mage::app()->getRequest()->getControllerName();
        $module 		= Mage::app()->getRequest()->getModuleName();
        $action 		= Mage::app()->getRequest()->getActionName();
        $route 			= Mage::app()->getRequest()->getRouteName();
        $status         = Mage::getStoreConfig('tagmanager/settings/status');
        $tracking_code  = Mage::getStoreConfig('tagmanager/settings/id');
        $affiliation    = Mage::getStoreConfig('tagmanager/settings/affiliation');

        if(!empty($affiliation)){
            $affiliation    = Mage::getStoreConfig('tagmanager/settings/affiliation');
        } else {
            $affiliation    = "Brasnthings";
        }

        switch($module) :
            case "cms" :
                $_pagetype = "home";
                break;
            case "catalog" :
                if($controller == "category") :
                    $_pagetype = "category";
                else :
                    $_pagetype = "product";
                endif;
                break;
            case "checkout" :
                $_pagetype = "cart";
                break;
            case "customer" :
                $_pagetype = "my account";
                break;
            case "wishlist" :
                $_pagetype = "wishlist";
                break;
            case "store" :
                $_pagetype = "store locator";
                break;
            case "contacts" :
                $_pagetype = "contact";
                break;
            case "giftcard" :
                $_pagetype = "customer";
                break;
            case "sales" :
                $_pagetype = "sales";
                break;
            case "rma" :
                $_pagetype = "my returns";
                break;
            case "newsletter" :
                $_pagetype = "newsletter";
                break;
            case "faq" :
                $_pagetype = "faq";
                break;
            case "paypal" :
                $_pagetype = "paypal";
                break;
            default:
                $_pagetype = "";
                break;
        endswitch;
    ?>
        <?php
        if(($controller == 'product') && ($action == 'view') && ($route == 'catalog') && ($module == 'catalog'))
        {
            $_product = Mage::registry('current_product');
            $product_name = $_product->getName();
            if($_product->getSize())
                $product_name .= " " . $_product->getSize();
            ?>
            <script type="text/javascript">
                //<![CDATA[
                dataLayer = [{
                    'productName': '<?php echo trim($product_name); ?>',
                    'PageType': 'Product'
                }];
                //]]>
            </script>
        <?php
        } else if($module == "cms" && $controller == "index" && $action == "noRoute") {
        ?>
        <script type="text/javascript">
            dataLayer = [{
                'Error': '404',
                'PageType': '404'
            }];

            dataLayer.push({'pageTitle': '404 Not Found'});
        </script>
        <?php
        } else if($controller == 'onepage' && $action == 'success' && $module == 'checkout') {

            $orderIdWithPrefix = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
            $order = Mage::getModel('sales/order')->load($orderId);
            $_totalData = $order->getData();
            $payment    = $order->getPayment()->getMethod();
            $transactionTotal 		= Mage::helper('core')->currency($_totalData['grand_total'],true,false);
            $transactionTax 		= Mage::helper('core')->currency($_totalData['tax_amount'],true,false);
            $transactionShipping 	= Mage::helper('core')->currency($_totalData['shipping_amount'],true,false);
            if ($_totalData['grand_total']>0)
            {
            $transactionTotal=Mage::helper('core')->currency($_totalData['grand_total']-$_totalData['tax_amount']-$_totalData['shipping_amount'],true,false);
            }
            //print_r('tohtml '.$transactionTotal);
            $_items = $order->getAllItems();
            $payment_method         = '';

            switch($payment){
                case 'checkmo':
                    $payment_method    = 'MO';
                    break;
                case 'paypal_express':
                    $payment_method     = 'PP';
                    break;
                case 'ewayau_direct':
                    $payment_method     = 'CC';
                    break;
                case 'eway_rapid':
                    $payment_method     = 'CC';
                    break;
                case 'anz_egate':
                    $payment_method     = 'CC';
                    break;
            }

        ?>

            <script type="text/javascript">
                dataLayer = [{
                    'PageType': 'Success',
                    'transactionId': '<?php echo $orderIdWithPrefix; ?>',
                    'transactionAffiliation': '<?php echo $affiliation ?>',
                    'transactionTotal': '<?php echo $transactionTotal; ?>',
                    'transactionTax': '<?php echo $transactionTax; ?>',
                    'transactionShipping': '<?php echo $transactionShipping; ?>',
                    'transactionPaymentType': '<?php echo $payment_method; ?>',
                    'transactionProducts': [
                        <?php
                        $j = 1;
                        $itm_count = count($_items);
                        if($itm_count > 0)
                        {
                            $parents = array();
                            foreach ($_items as $_item){
                                $oProduct 	= null;
                                $product_id =  $_item->getProductId();
                                $oProduct 	= Mage::getModel('catalog/product')->load($product_id);
                                $CatIds 	= $oProduct->getCategoryIds();

                                $parentId   = $_item->getHasChildren();
                                
                                if($parentId == false || ($parentId == true && $oProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)){

                                    if(!empty($CatIds))
                                    {
                                        $str_cat_names = "";
                                        $cat_count = count($CatIds);
                                        $i = 1;
                                        foreach($CatIds as $CatId)
                                        {
                                            $oCategory = Mage::getModel('catalog/category')->load($CatId);
                                            $str_cat_names .= $oCategory->getName();
                                            if($i < $cat_count)
                                                $str_cat_names .= ",";

                                            $i++;
                                        }

                                        $product_name = $_item->getName();

                                        $itemPrice = $_item->getPrice();
                                        $parentItem = $_item->getParentItemId();
                                        if(!empty($parents[$parentItem])){
                                            $parentItem = $parents[$parentItem];
                                            $itemPrice = $parentItem->getPrice();
                                        }
                                    ?>
                                    {

                                    'sku': '<?php echo $_item->getSku(); ?>',
                                    'name': '<?php echo $product_name; ?>',
                                    'category': <?php echo json_encode($str_cat_names); ?>,
                                    'price': '<?php echo Mage::helper('core')->currency($itemPrice,true,false); ?>',
                                    'quantity': '<?php echo (int) $_item->getQtyOrdered(); ?>'

                                    <?php
                                    if($j < $itm_count)
                                        echo "},";
                                     else
                                         echo '}';

                                    $j++;
                                ?>
                        <?php
                               }
                            }else{
                                $parents[$_item->getItemId()] = $_item;
                            }
                         }
                    }?>
                    ]
                }];
            </script>
    <?php
        }
	    //SEO - Google Analytics Implementation


        else {
        ?>
            <script type="text/javascript">
                //<![CDATA[
                dataLayer = [{
                    'PageType': '<?php echo $_pagetype; ?>'
                }];
                //]]>
            </script>
        <?php
        }
        ?>

    <?php
        if (!$this->isGoogleTagmanagerAvailable(Mage::app()->getStore())) {
            return '';
        }
        $accountId = Mage::getStoreConfig(self::XML_PATH_ACCOUNT);

        return '
<!-- BEGIN GOOGLE TAG MANAGER CODE  -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id='.$accountId.'" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=\'//www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,\'script\',\'dataLayer\',\''.$accountId.'\');</script>
' . $this->_TagManagerOrderTrackingCode() . '
<!-- END GOOGLE TAG MANAGER CODE -->';

    }
}