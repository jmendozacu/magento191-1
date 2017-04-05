<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gayan
 * Date: 7/24/13
 * Time: 9:31 AM
 * To change this template use File | Settings | File Templates.
 */

class Netstarter_Pinterest_IndexController extends Mage_Core_Controller_Front_Action{

    public function indexAction(){
        try {
        $response = array();
            if($path = $this->getRequest()->getParam('url')){
                $firstPageLimit = 1;
                $lastPageLimit = 10;
                $checkPage = explode('?',$path);
                if(isset($checkPage[1]) && preg_match('/p=/',$checkPage[1])){
                    $pagers = explode('p=',$checkPage[1]);
                    if(isset($pagers[1])&& is_numeric($pagers[1])) {
                        $lastPageLimit = $lastPageLimit * $pagers[1];
                        $firstPageLimit = $lastPageLimit-10;
                    }
                }
                $requestPath = new Zend_Controller_Request_Http($checkPage[0]);
                $requestPath = $requestPath->getRequestUri();
                $requestCases = array();
                $origSlash = (substr($requestPath, -1) == '/') ? '/' : '';
                $requestPath = trim($requestPath, '/');
                // If there were final slash - add nothing to less priority paths. And vice versa.

                $altSlash = $origSlash ? '' : '/';

                $requestCases[] = $requestPath . $origSlash;
                $requestCases[] = $requestPath . $altSlash;

                $url_model1 = Mage::getModel('core/url_rewrite')
                    ->getCollection()
                    ->addFieldToFilter(
                        array('request_path', 'request_path'),
                        array(
                            array('like'=>'%'.$requestCases[0]),
                            array('like'=>'%'.$requestCases[1])
                        ))
                  ->getFirstItem();

                $provider_name = Mage::getStoreConfig('general/store_information/name',Mage::app()->getStore()->getId());
                $currency_code = Mage::app()->getStore(Mage::app()->getStore()->getId())->getCurrentCurrencyCode();
                if($url_model1->getCategoryId() && !$url_model1->getProductId()){

                    $category = Mage::getModel('catalog/category')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->load($url_model1->getCategoryId());
                    
                    $catProduct = $category->getProductCollection()
                        ->addStoreFilter(Mage::app()->getStore()->getId())
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('visibility', array('neq' => 1))
                        ->addAttributeToFilter('status', 1)
                        ->addAttributeToFilter('is_saleable', TRUE)
                        ->addPriceData()
                        ->addFieldToFilter('price', array('gt' => '0.00'))
                        ->setPage($firstPageLimit, $lastPageLimit)
                        ->setPageSize(10);

                    $response['provider_name'] = $provider_name;
                    $response['url'] = $path;

                    $products = array();
                    $offers = array();

                    foreach ($catProduct as $item)
                    {

                        if($item->getPrice() != 0.0000 && $item->isSaleable()){
                            $products['title'] = $item->getName();
                            $products['product_id'] = $item->getSku();
                            if($item->getNewsToDate()) $products['product_expiration'] = $item->getNewsToDate();


                            if ( $item->getTypeId() == 'simple'){
                                if($item->getNewsToDate()) $products['product_expiration'] = $item->getNewsToDate();

                                $offers['description'] = $item->getShortDescription();
                                $offers['price'] = $item->getPrice();
                                $offers['currency_code'] = $currency_code;
                                $offers['availability'] = $item->isSaleable()?'in stock':'out of stock';
                                $offers['sale_start_date'] = $item->getNewsFromDate()?$item->getNewsFromDate():$item->getCreatedAt();
                                $offers['sale_end_date'] = $item->getNewsToDate();
                                $offers['images'] = $item->getImageUrl();

                                $products['offers'][] =$offers;
                                $response['products'][] =$products;

                            }else if ( $item->getTypeId() == 'configurable'){
                                if($item->getNewsToDate()) $products['product_expiration'] = $item->getNewsToDate();

                                $offers['description'] = $item->getShortDescription();
                                $offers['price'] = $item->getPrice();
                                $offers['currency_code'] = $currency_code;
                                $offers['availability'] = $item->isSaleable()?'in stock':'out of stock';
                                $offers['sale_start_date'] = $item->getNewsFromDate()?$item->getNewsFromDate():$item->getCreatedAt();
                                $offers['sale_end_date'] = $item->getNewsToDate();
                                $offers['images'] = $item->getImageUrl();

                                $products['offers'][] =$offers;
                                $response['products'][] =$products;

                            }else if ( $item->getTypeId() == 'grouped'){

                                $associatedProducts = $item->getTypeInstance(true)->getAssociatedProducts($item);

                                foreach($associatedProducts as $listproduct){
                                    $offers['description'] = $listproduct->getShortDescription();
                                    if($listproduct->getOpenAmountMin()) {$offers['price'] = $listproduct->getOpenAmountMin();}
                                    if($listproduct->getOpenAmountmax()){  $offers['price'] = $listproduct->getOpenAmountmax();}
                                    if(!$listproduct->getOpenAmountMin() && !$listproduct->getOpenAmountmax()){ $offers['price'] = '50.00';}
                                    $offers['currency_code']= $currency_code;
                                    $offers['availability'] = $listproduct->isSaleable()?'in stock':'out of stock';
                                    $offers['sale_start_date'] = $listproduct->getNewsFromDate()?$listproduct->getNewsFromDate():$listproduct->getCreatedAt();
                                    $offers['sale_end_date'] = $listproduct->getNewsToDate();

                                    $products['offers'][] =$offers;
                                }
                                $response['products'][] =$products;

                            }else if ( $item->getTypeId() == 'giftcard'){
                                if($item->getNewsToDate()) $products['product_expiration'] = $item->getNewsToDate();
                                $offers['description'] = $item->getShortDescription();
                                $price = '';
                                if($item->getOpenAmountMin()){ $price .=  'from '.$item->getOpenAmountMin(); }
                                if($item->getOpenAmountMax()){ $price .=  ' to '.$item->getOpenAmountMax(); }
                                $offers['price'] = $price;
                                $offers['currency_code'] = $currency_code;
                                $offers['availability'] = $item->isSaleable()?'in stock':'out of stock';
                                $offers['sale_start_date'] = $item->getNewsFromDate()?$item->getNewsFromDate():$item->getCreatedAt();
                                $offers['sale_end_date'] = $item->getNewsToDate();
                                $offers['images'] = $item->getImageUrl();

                                $products['offers'][] =$offers;
                                $response['products'][] =$products;

                            }
                        }
                    }

                }else if($url_model1->getProductId()){
                    // Filter the url_path with product permalink
                    $products = Mage::getModel('catalog/product')
                        ->getCollection()
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->addAttributeToFilter('entity_id', array('in' => $url_model1->getProductId()))
                        ->addAttributeToSelect('*');
                    foreach ($products as $item)
                    {

                        if ( $item->getTypeId() == 'simple'){


                            $response['provider_name'] =  $provider_name;
                            $response['url'] = $item->getProductUrl();
                            $response['title'] = $item->getName();
                            $response['description'] = $item->getShortDescription();
                            $response['product_id'] = $item->getSku();
                            $response['price'] = $item->getPrice();
                            $response['currency_code'] = $currency_code;
                            $response['availability'] = $item->isSaleable()?'in stock':'out of stock';
                            $response['sale_start_date'] = $item->getNewsFromDate()?$item->getNewsFromDate():$item->getCreatedAt();
                            $response['sale_end_date'] = $item->getNewsToDate();
                            $response['images'] = $item->getImageUrl();

                        }else if ( $item->getTypeId() == 'configurable'){

                            $response['provider_name'] =  $provider_name;
                            $response['url'] = $item->getProductUrl();
                            $response['title'] = $item->getName();
                            $response['description'] = $item->getShortDescription();
                            $response['product_id'] = $item->getSku();
                            $response['price'] = $item->getPrice();
                            $response['currency_code'] = $currency_code;
                            $response['availability'] = $item->isSaleable()?'in stock':'out of stock';
                            $response['sale_start_date'] = $item->getNewsFromDate()?$item->getNewsFromDate():$item->getCreatedAt();
                            $response['sale_end_date'] = $item->getNewsToDate();
                            $response['images'] = $item->getImageUrl();


                        }else if ( $item->getTypeId() == 'grouped'){
                            $products = array();
                            $offers = array();

                            $response['provider_name'] =  $provider_name;
                            $response['url'] = $item->getProductUrl();
                            $products['title'] = $item->getName();
                            $products['product_id'] = $item->getSku();
                            if($item->getNewsToDate()) $products['sale_end_date'] = $item->getNewsToDate();

                            $associatedProducts = $item->getTypeInstance(true)->getAssociatedProducts($item);

                            foreach($associatedProducts as $listproduct){
                                $offers['product_id'] = $item->getSku();
                                $offers['description'] = $item->getShortDescription();
                                $offers['currency_code']= $currency_code;
                                if($listproduct->getOpenAmountMin() && !$listproduct->getOpenAmountMin() != "00.00"){$offers['price'] = $listproduct->getOpenAmountMin();}
                                if($listproduct->getOpenAmountmax()){  $offers['price'] = $listproduct->getOpenAmountmax();}
                                $offers['availability'] = $item->isSaleable()?'in stock':'out of stock';
                                $offers['sale_start_date'] = $item->getNewsFromDate()?$item->getNewsFromDate():$item->getCreatedAt();
                                $offers['sale_end_date'] = $item->getNewsToDate();
                                $products['offers'][] =$offers;
                            }
                            $products['images'] = $item->getImageUrl();
                            $response['products'][] =$products;


                        }else if ( $item->getTypeId() == 'giftcard'){
                            $response['provider_name'] =  $provider_name;
                            $response['url'] = $item->getProductUrl();
                            $products['title'] = $item->getName();
                            $products['product_id'] = $item->getSku();

                            $products['description'] = $item->getShortDescription();
                            $price = '';
                            if($item->getOpenAmountMin()){ $price .=  'from '.$item->getOpenAmountMin(); }
                            if($item->getOpenAmountMax()){ $price .=  ' to '.$item->getOpenAmountMax(); }
                            $products['price'] = $price;
                            $products['currency_code'] = $currency_code;
                            $products['availability'] = $item->isSaleable()?'in stock':'out of stock';
                            $products['sale_start_date'] = $item->getNewsFromDate()?$item->getNewsFromDate():$item->getCreatedAt();
                            $products['sale_end_date'] = $item->getNewsToDate();
                            $products['images'] = $item->getImageUrl();
                            $response['products'][] =$products;
                        }
                    }
                }


        }
    } catch (Exception $e) {
        $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
        $this->getResponse()->setHeader('Status','404 File not found');
        $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_ROUTE_PAGE);
            if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
                $this->_forward('defaultNoRoute');
            }
        return ;
    }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return ;
    }
}