<?php
/**
 * Class SearchController
 * enable ajax filter and full page cache for search
 * only query search
 * @author bzhang@netstarter.com.au
 */
require_once 'Mage/CatalogSearch/controllers/ResultController.php';

class BZ_Navigation_ResultController extends Mage_CatalogSearch_ResultController
{
    public function indexAction()
    {
        $enable = Mage::getStoreConfig('bz_navigation/general/enabled');
        if(!$enable) return parent::indexAction();
        $isAjax = $this->getRequest()->isAjax();
        $query = Mage::helper('catalogsearch')->getQuery();
        //$searchSession = Mage::getSingleton('catalogsearch/session');
        /* @var $query Mage_CatalogSearch_Model_Query */
        $query->setStoreId(Mage::app()->getStore()->getId());
        if ($query->getQueryText() != '') {
            $helper = Mage::helper('bz_navigation');
            $search_path = $helper->getSearchPath();
            //redirect to SEO friendly one if it is not
            $paths = explode('/',trim($this->getRequest()->getPathInfo(),'/'));
            if($search_path && isset($paths[0]) && $paths[0] != strtolower($search_path)){
                $url = Mage::getBaseUrl().$search_path.'/'.$helper->labelEncode($query->getQueryText()).'/';
                $this->getResponse()->setRedirect($url);
                return;
            }
            if (Mage::helper('catalogsearch')->isMinQueryLength()) {
                $query->setId(0)
                    ->setIsActive(1)
                    ->setIsProcessed(1);
            }
            else {
                if ($query->getId()) {
                    $query->setPopularity($query->getPopularity()+1);
                }
                else {
                    $query->setPopularity(1);
                }

                if ($query->getRedirect()){
                    $query->save();
                    $this->getResponse()->setRedirect($query->getRedirect());
                    return;
                }
                else {
                    $query->prepare();
                }
            }
            Mage::helper('catalogsearch')->checkNotes();
            if($isAjax){
                $params = $this->getRequest()->getParams();
                $this->loadLayout();
                $this->_initLayoutMessages('catalog/session');
                $this->_initLayoutMessages('checkout/session');
                $this->renderLayout();//to prevent output buffere cache
                Mage::getSingleton('catalog/session')->setParamsMemorizeDisabled(0);
                $left = $this->getLayout()->getBlock('left')->toHtml();
                $right = $this->getLayout()->getBlock('right')->toHtml();
                $content = $this->getLayout()->getBlock('content')->toHtml();
                $head = $this->getLayout()->getBlock('head');
                $title = $head->getTitle();
                $meta_description =  $head->getDescription();
                $meta_keyword = $head->getKeywords();
                $robots = $head->getRobots();
                $meta_data = array('robots'=>$robots,'title'=>$title,'description'=>$meta_description,'keywords'=>$meta_keyword);
                $ajax = array('left'=>$left,'right'=>$right, 'content'=>$content, 'redirect'=>0, 'meta_data'=>$meta_data);
                $this->getResponse()->setBody(json_encode($ajax));
                if (!Mage::helper('catalogsearch')->isMinQueryLength()) {
                    $query->save();
                }
            }else{
                $this->loadLayout();
                $this->_initLayoutMessages('catalog/session');
                $this->_initLayoutMessages('checkout/session');
                $this->renderLayout();
                if (!Mage::helper('catalogsearch')->isMinQueryLength()) {
                    $query->save();
                }
            }
        }
        else {
            if($isAjax){
                $refererUrl = $this->_getRefererUrl();
                if (empty($refererUrl)) {
                    $refererUrl = Mage::getBaseUrl();
                }
                $ajax = array('left'=>'', 'content'=>'', 'redirect'=>$refererUrl);
                $this->getResponse()->setBody(json_encode($ajax));
            }else{
                $this->_redirectReferer();
            }
        }
    }
}
