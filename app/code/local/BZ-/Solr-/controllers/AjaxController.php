<?php
/**
 * Class AjaxController
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Solr_AjaxController extends Mage_Core_Controller_Front_Action
{
    public function suggestAction()
    {
        if (!$this->getRequest()->getParam('q', false)) {
            $this->getResponse()->setRedirect(Mage::getSingleton('core/url')->getBaseUrl());
        }
        $helper = Mage::helper('bz_solr');
        if(!$helper || !$helper->isThirdPartyEngineAvailable()){
            $this->getResponse()->setBody('<ul class="result-list"><li>Sorry, Search engine is turned off or not available.</li></ul>');
            return;
        }
        $q = $this->getRequest()->getParam('q', false);
        $model = Mage::getModel('bz_solr/search'); 
        $result = $model->simpleSearch($q, 0, array(), 9);
        $store = Mage::app()->getStore();
        $locale = $store->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
        $languageSuffix = Mage::helper('bz_solr')->getLanguageSuffix($locale);
        $product_suffix = Mage::getStoreConfig('catalog/seo/product_url_suffix');
        //$html = '<ul class="result-list">';
        $html = "";
        if(isset($result['groups']) && !empty($result['groups'])){
            $sugg_html  = "";
            $prod_html  = "";
            $cat_html   = "";
            $cms_html   = "";
            if(isset($result['suggestions']) && !empty($result['suggestions'])){                
                $sugg_html .= '<ul class="product-results">';
                $sugg_html .= '<li class="top-title">Did you mean?</li>';
                $script = 'onclick="$(\'search\').value=this.innerHTML;this.preventDefault();"';

                $sugg_count = count($result['suggestions']);
                $count_flag = 0;
                foreach($result['suggestions'] as $v){
                    ++$count_flag;
                    $separator = "";
                    if($sugg_count != $count_flag)
                        $separator = ",&nbsp;";

                    $sugg_html .= '<li><span href="#" '.$script.'>'. $v .'</span>'.$separator.'</li>';
                }
                $sugg_html .= '</ul>';
            }
            foreach($result['groups'] as $r){
                if (isset($r['groupValue']) && $r['groupValue'] == 'category') {
                    $cat_html .= '<ul class="categories">';
                    $cat_html .= '<li class="title">Related Categories</li>';
                    foreach($r['doclist']['docs'] as $d){
                        $d = (array)$d;
                        $url = Mage::getBaseUrl() . $d['url_path' . $languageSuffix];
                        $name = $d['name_path'.$languageSuffix];
                        $cat_html .='<li><a href="' . $url . '">' . $name.'</a></li>';
                    }
                    $cat_html .= '</ul>';
                }
                elseif(isset($r['groupValue']) && $r['groupValue'] == 'product') {
                    $prod_html .= '<ul class="products">';
                    $prod_html .= '<li class="title"><span>Are you looking for...</span><span class="view-all-results" onclick="document.getElementById(\'search_mini_form\').submit();">View All Search Results</span></li>';
                    foreach($r['doclist']['docs'] as $d){
                        $d = (array)$d;
                        $thumbnail = $d['thumbnail'.$languageSuffix];
                        $image_obj = Mage::getModel('catalog/product_image')
                            ->setDestinationSubdir('thumbnail')
                            ->setBaseFile($thumbnail)
                            ->setWidth(170)
                            ->setHeigth(170);

                        $_product = Mage::getModel('catalog/product')->load($d['id']);
                        $_prodResource = $_product->getResource();
                        $brand = "";
                        $brand = $_prodResource->getAttribute('brand')->getSource()->getOptionText($_product->getBrand());
                        $size = $_product->getSize();
                        //loading from cached file without save and resized again
                        if($image_obj->isCached()){
                            $src = $image_obj->getUrl();
                        }else{
                            $src = $image_obj->resize()->saveFile()->getUrl();
                        } 
                        $url = Mage::getBaseUrl() . $d['url_key' . $languageSuffix];
                        if($product_suffix) $url .= '.'.str_replace('.','',$product_suffix);
                        if(isset($d['price_0_1'])) $formattedPrice = Mage::helper('core')->currency($d['price_0_1'], true, false);
                        else $formattedPrice = '';
                        $name = $d['display_name'.$languageSuffix];
                        $prod_html .='<li><a href="' . $url . '"><img width="100" src="'.$src.'" alt="image" /></a>
                                <a href="' . $url . '"><span class="brand">' . $brand. '</span><span class="name">' . $name. '</span><span class="price">'. $formattedPrice.'</span></a>
                                    </li>';
                    }
                    $prod_html .= '</ul>';
                }
                elseif (isset($r['groupValue']) && $r['groupValue'] == 'cms') {
                    $cms_html .= '<ul class="cms-pages">';
                    $cms_html .= '<li class="title">Related Info</li>';
                    foreach($r['doclist']['docs'] as $d){
                        $d = (array)$d;
                        $url = Mage::getBaseUrl() . $d['identifier' . $languageSuffix];
                        $name = $d['title'.$languageSuffix];
                        $cms_html .='<li><a href="' . $url . '">' . $name.'</a></li>';
                    }
                    $cms_html .= '</ul>';
                }
            }
            $html = $sugg_html . $prod_html . $cat_html . $cms_html;
        }
        elseif(isset($result['suggestions']) && !empty($result['suggestions'])){
            $html .= '<ul class="product-results">';
            $html .= '<li class="top-title">Did you mean?</li>';
            $script = 'onclick="$(\'search\').value=this.innerHTML;this.preventDefault();"';

            $sugg_count = count($result['suggestions']);
            $count_flag = 0;
            foreach ($result['suggestions'] as $v) {
                ++$count_flag;
                $separator = "";
                if($sugg_count != $count_flag)
                	$separator = ",&nbsp;";
                	                
                $html .= '<li><span ' . $script . '>' . $v . '</span>'. $separator .'</li>';
            }
            $html .= '</ul>';
        }
        //$html .= '</ul>';
        $this->getResponse()->setBody($html);
    }
}
