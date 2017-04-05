<?php
/**
 * Class Router
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{
    /**
     * Initialize Controller Router
     *
     * @param Varien_Event_Observer $observer
     */
    public function initControllerRouters($observer)
    {
        /* @var $front Mage_Core_Controller_Varien_Front */
        $front = $observer->getEvent()->getFront();
        //adding our custom router in the loop and we make our own match and nice URL SEO
        $front->addRouter('bz_navigation', $this);
    }
    
    /**
     * getting catalog url suffix
     * @return string
     */
    protected function _getUrlSuffix(){
        $suffix = '';
        $suffix .= Mage::getStoreConfig('catalog/seo/category_url_suffix');
        //if it is EE1.13.0.0+ then it need to add a dot '.';
        if (strstr(Mage::getVersion(), '1.13') && $suffix) {
            $suffix = '.' . $suffix;
        }
        return $suffix;
    }
    
    /**
     * 
     * @param type $paths
     * @param type $use_separator
     * @param type $separator
     * @return array('filters'=>array(),'category_paths'=>array())
     */
    protected function _loadCategoryAndFilters($paths, $use_separator = false, $separator=''){
        $filters = array();
        $category_paths = array();
        if($use_separator && !empty($separator)){
            // www.domain.com/cat1/cat2/where/filter1/value1/filter2/value2
            $separator_found = false;
            foreach($paths as $p){
                if($p != $separator && $separator_found === false){
                    $category_paths[] = $p;
                }elseif($p == $separator){
                    $separator_found = true;
                    continue;
                }else{
                    $filters[] = $p;
                }
            }
        }
        else{
            //other method www.domain.com/cat1/cat2/filter1/value1/filter2/value2/1 the last 2 means the filter numbers then we will know the cat path
            $num = end($paths);
            if(is_numeric($num)){
                $num = (int) $num;
                $count = count($paths);
                //at least one cat and one pair of filter www.xx.com/cat1/filter/value/1
                if (($count - $num * 2) < 1) {
                    return false;
                } else {
                    $cat_num = $count - $num * 2 - 1;
                    $cnt = 0;
                    foreach ($paths as $p) {
                        if ($cnt < $cat_num) {
                            $category_paths[] = $p;
                        } elseif ($cnt != $count - 1) {
                            $filters[] = $p;
                        }
                        $cnt++;
                    }
                }
            }
        }
        return array('filters'=>$filters,'category_paths'=>$category_paths);
    }

    /**
     * Validate and Match Filter rewrite URL and modify request
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        if (!Mage::isInstalled()) {
            //as cms rounter will do install here just do nothing here
            return false;
        }
        $enable = Mage::getStoreConfig('bz_navigation/general/enabled');
        if (!$enable) {
            return false;
        }
        $path = trim($request->getPathInfo(),'/');
        $front = $this->getFront();
        $path_arr = explode('/',$path);
        $search_path = Mage::getStoreConfig('bz_navigation/general/search_url');
        if (isset($path_arr[0]) && strtolower($path_arr[0]) == strtolower($search_path)) {
            return $this->_matchSearch($request, $front);
        }
        $suffix = $this->_getUrlSuffix();
        $use_separator = Mage::getStoreConfig('bz_navigation/general/use_separator');
        $separator = $use_separator ? Mage::getStoreConfig('bz_navigation/general/url_separator'): '';
        //clean the path to remove suffix
        if(!empty($suffix)){
            $path = preg_replace('/'.$suffix.'$/', '', $path);
        }
        //separator is used to determine which part is category which part is not
        $paths = explode('/', $path);
        $paths_all = $this->_loadCategoryAndFilters($paths,$use_separator,$separator);
        $category_paths = isset($paths_all['category_paths'])? $paths_all['category_paths'] : array();
        $filters = isset($paths_all['filters'])? $paths_all['filters'] : array();
        $cat_path_str = implode('/', $category_paths) . $suffix;
        /**
         * EE1.13.0.0 now no duplicate URL as all category url is in Enterprise_UrlRewrite_Model_Url_Rewrite
         */
        if(strstr(Mage::getVersion(),'1.13')){
            $ee_paths = Mage::getModel('enterprise_urlrewrite/url_rewrite_request')->getSystemPaths($cat_path_str);
            $ee_rewrite = Mage::getModel('enterprise_urlrewrite/url_rewrite');
            $obj = $ee_rewrite->loadByRequestPath($ee_paths);
            $targetPath = $obj->getTargetPath();
            if ($targetPath) {
                $t_path = explode('/', $targetPath);
            }
            if (!empty($t_path) && isset($t_path[1]) && $t_path[1] == 'category' && isset($t_path[4])) {
                $cat_id = $t_path[4];
            } else {
                return false;
            }
        }else{
            $core_rewrite = Mage::getModel('core/url_rewrite');
            if ($core_rewrite->getStoreId() === null || $core_rewrite->getStoreId() === false) {
                $core_rewrite->setStoreId(Mage::app()->getStore()->getId());
            }
            $obj = $core_rewrite->loadByRequestPath($cat_path_str);
            $cat_id = $obj->getCategoryId();
        }
        //if found then update controller parameters
        if ($cat_id) {
            $controllerClassName = $this->_validateControllerClassName('BZ_Navigation', 'category');
            $controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $front->getResponse());
            $request->setModuleName('catalog')
                    ->setRouteName('catalog')
                    ->setControllerName('category')
                    ->setActionName('view')
                    ->setControllerModule('BZ_Navigation')
                    ->setParam('id', $cat_id);
            $params = array();
            for ($i = 0, $end = count($filters); $i < $end; $i += 2) {
                $params[$filters[$i]] = isset($filters[$i + 1]) ? $filters[$i + 1] : null;
            }
            foreach($params as $k => $v){
                if ($k != null && $v != null) {
                    $request->setParam($k, $v);
                }
            }
            //set alias for url rewrite getUrl() otherwise it will be wrong in frontend
            $request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $path);
            $request->setAlias('CATALOG_CATEGORY_SHORT_PATH', $cat_path_str);           
            //fire the controller not to go back to standard
            $request->setDispatched(true);
            $controllerInstance->dispatch('view');
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Validate and Match Filter rewrite URL and modify request
     * for CatalogSearch result page only for the query search not advanced search result
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    protected function _matchSearch(Zend_Controller_Request_Http $request, $front){
        $path = trim($request->getPathInfo(),'/');
        $paths = explode('/',$path);
        $helper = Mage::helper('bz_navigation');
        $search_path = $helper->getSearchPath();
        if (!isset($paths[0]) || $paths[0] != strtolower($search_path)) {
            return false;
        }
        //convert to pair
        $controllerClassName = $this->_validateControllerClassName('BZ_Navigation', 'Result');
        $controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $front->getResponse());
        $request->setModuleName('catalogsearch')
                ->setRouteName('catalogsearch')
                ->setControllerName('result')
                ->setActionName('index')
                ->setControllerModule('BZ_Navigation');
        $params = array();
        if (isset($paths[1]) && !empty($paths[1])) {
            $params['q'] = urldecode($paths[1]);
        }
        for ($i = 2; $i < count($paths); $i += 2) {
            $params[$paths[$i]] = isset($paths[$i + 1]) ? $paths[$i + 1] : null;
        }
        foreach ($params as $k => $v) {
            if ($k != null && $v != null) {
                $request->setParam($k, $v);
            }
        }
        //set alias for url rewrite getUrl() otherwise it will be wrong in frontend
        $request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $path);
        if (isset($paths[1])) {
            $clean_path = $paths[0] . '/' . $paths[1] . '/';
        } else {
            $clean_path = '';
        }
        $request->setAlias('CATALOG_CATEGORY_SHORT_PATH', $clean_path);
        //fire the controller not to go back to standard
        $request->setDispatched(true);
        $controllerInstance->dispatch('index');
        return true;
    }
}
