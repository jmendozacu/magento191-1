<?php
/**
 * Class Data
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Navigation_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_facet_separator = '_';
    protected $_suffix = null;
    protected $_url_separator = null;
    protected $_use_url_separator = null;
    protected $_category_mode = null;
    protected $_search_url = null;
    protected $_search_path = null;
    protected $_filter_settings = null;
    
    public function getCategoryMode(){
        if($this->_category_mode === null){
            $this->_category_mode = Mage::getStoreConfig('bz_navigation/general/category_mode');
        }
        return $this->_category_mode;
    }
    
    public function getCatalogSuffix(){
        if($this->_suffix === null){
            $this->_suffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');
            if(strstr(Mage::getVersion(),'1.13') && $this->_suffix) $this->_suffix = '.'.$this->_suffix;
        }
        return $this->_suffix;
    }
    
    public function useUrlSeparator(){
        if($this->_use_url_separator === null){
            $this->_use_url_separator = Mage::getStoreConfig('bz_navigation/general/use_separator');
        }
        return $this->_use_url_separator;
    }
    
    public function getUrlSeparator(){
        if($this->_url_separator === null){
            $this->_url_separator = Mage::getStoreConfig('bz_navigation/general/url_separator');
        }
        return $this->_url_separator;
    }
    
    public function getFacetSeparator(){
        return $this->_facet_separator;
    }
    
    public function useAjax(){
        if($this->_use_ajax === null){
            $this->_use_ajax = Mage::getStoreConfig('bz_navigation/general/ajax');
        }
        return $this->_use_ajax;
    }
    
    //mangeto reserved keys
    //@param Mage_Core_Controller_Request_Http $request
    public function getReserveKeys($request){
        $reserve = array('dir','order','mode','limit','p','id','ajax','isAjax','no_cache','___sotre','___SID','___from_store','q');
        //removing the ?query params
        $query_arr = $request->getQuery();
        return array_merge($reserve,array_keys($query_arr));
    }

    public function getSearchPath(){
        if(is_null($this->_search_path)){
            $this->_search_path = Mage::getStoreConfig('bz_navigation/general/search_url');
        }
        return $this->_search_path;
    }

    /**
     * center all similar function into one
     * @param type $request
     * @param type $item
     * @return array(no_suffix_url, clean_up_url)
     */
    protected function _prepareItemUrl($request, $is_search){
        $url_route = $request->getAlias('CATALOG_CATEGORY_SHORT_PATH');
        if($is_search){
            $search_url = $this->getSearchUrl();
            if($search_url){
                $url = Mage::getBaseUrl() . $search_url;
                return array($url,$url);
            } else {
                return false;
            }
        } else {
            if ($url_route){
                $url = Mage::getBaseUrl() . $url_route;
            } else {
                $url = Mage::getUrl('*/*/*', array('_current' => false, '_use_rewrite' => true));
                // Remove the query string from REQUEST_URI
                if ($pos = strpos($url, '?')) {
                    $url = substr($url, 0, $pos);
                }
            }
            //remove suffix
            $suffix = $this->getCatalogSuffix();
            if($suffix) {
                $no_suffix_url = str_replace($suffix, '', $url);
                return array($no_suffix_url, $url);
            } else return array($url,$url);
        }
    }


    /**
     * 
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Catalog_Model_Layer_Filter_Item $item
     */
    public function getAttributeItemUrl($request,$item){
        //check is search page
        $is_search = false;
        if($request->getModuleName() == 'catalogsearch') {
            $is_search = true;
        }
        list($url_no_suffix, $url) = $this->_prepareItemUrl($request, $is_search);
        
        //use attribute label rather than attribute code for better SEO but customer need to input unique name
        $attribute_label = trim($item->getFilter()->getName());
        if($attribute_label) $attribute_label = $this->labelEncode($attribute_label);
        else $attribute_label = $item->getFilter()->getRequestVar();
        //parsing parameters
        $params = $request->getParams();
        $params_keys = array_keys($params);
        $multi_facet_separator = $this->getFacetSeparator();
        $label = $item->getLabel();
        $clean_label = $this->labelEncode($label);
        $url_value = '';
        //if multi_facet then sort the multiple options
        if ($multi_facet_separator && $attribute_label) {
            if (in_array($attribute_label, $params_keys)) {
                $multi_facets = explode($multi_facet_separator, $params[$attribute_label]);
                //check option is selected or not
                if(($key = array_search($clean_label, $multi_facets)) !== false) {
                    $item->setData('selected', true);
                    //select option then return url with unselect
                    unset($multi_facets[$key]);
                }
                else {
                    $item->setData('selected', false);
                    $multi_facets[] = $clean_label;
                }
                if(!empty($multi_facets)){
                    asort($multi_facets);
                    $url_value = implode($multi_facet_separator, $multi_facets);
                }
            } else {
                $url_value = $clean_label;
            }
        } else {
            $url_value = $clean_label;
        }
        $reserved_queries = $this->getReserveKeys($request);
        //unset reserved keys and set params
        foreach($reserved_queries as $key){
            if(isset($params[$key])) unset($params[$key]);
        }
        //if the parameter is empty then remove from path
        if($url_value === '' && isset($params[$attribute_label])) unset($params[$attribute_label]);
        else $params[$attribute_label] = $url_value;
        ksort($params);
        //building urls
        if(count($params)>0){
            $return_url = rtrim($url_no_suffix,'/').'/';
            if($this->useUrlSeparator() && $this->getUrlSeparator() && !$is_search){
                $return_url .= $this->getUrlSeparator().'/';
                foreach($params as $k=>$v) $return_url .= $k.'/'.$v.'/';
            }else{
                foreach($params as $k=>$v) $return_url .= $k.'/'.$v.'/';
                if(!$is_search) $return_url .= count($params);
            }
            return $return_url;
        }else{
            return $url;
        }
    }
    
    /**
     * 
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Catalog_Model_Layer_Filter_Item $item
     */
    public function getAttributeRemoveItemUrl($request,$item){
        $is_search = false;
        if($request->getModuleName() == 'catalogsearch') {
            $is_search = true;
        }
        list($url_no_suffix, $url) = $this->_prepareItemUrl($request, $is_search);
        //load params
        $params = $request->getParams();
        $params_keys = array_keys($params);
        //get attribute label if not use request key
        $attribute_label = trim($item->getFilter()->getName());
        $requestKey = $item->getFilter()->getRequestVar();
        if($attribute_label) $attribute_label = $this->labelEncode($attribute_label);
        else $attribute_label = $requestKey;
        if(in_array($attribute_label,$params_keys)){
            unset($params[$attribute_label]);
        }
        //the reserved key word will be saved in session so need to remove them
        $reserved_queries = $this->getReserveKeys($request);
        //unset reserved keys and set params
        foreach($reserved_queries as $key){
            if(isset($params[$key])) unset($params[$key]);
        }
        ksort($params);
        //building urls
        if(count($params)>0){
            $return_url = rtrim($url_no_suffix,'/').'/';
            if($this->useUrlSeparator() && $this->getUrlSeparator() && !$is_search){
                $return_url .= $this->getUrlSeparator().'/';
                foreach($params as $k=>$v) $return_url .= $k.'/'.$v.'/';
            }else{
                foreach($params as $k=>$v) $return_url .= $k.'/'.$v.'/';
                if(!$is_search) $return_url .= count($params);
            }
            return $return_url;
        }else{
            return $url;
        }
    }
    
    public function getSearchUrl()
    {
        if($this->_search_url === null){
            $this->_search_url = Mage::getStoreConfig('bz_navigation/general/search_url');
        }
        $queryText = urlencode(Mage::helper('catalogsearch')->getQueryText());
        return $this->_search_url.'/'.$queryText.'/';
    }
    
    /**
     * Must be one select for many reason not even possible to multiple select
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Catalog_Model_Layer_Filter_Item $item
     */
    public function getCategoryItemUrl($request,$item){

        $category_mode = $item->getPrapareUrl();
        if(!$category_mode)
            $category_mode = $this->getCategoryMode();

        $is_search = false;

        if($request->getModuleName() == 'catalogsearch') {
            $category_mode = 1;
            $is_search = true;
        }
        //category as filter
        if($category_mode == 1){
            list($url_no_suffix, $url) = $this->_prepareItemUrl($request, $is_search);   
            $attribute_label = $item->getFilter()->getName();
            if ($attribute_label){
                $attribute_label = $this->labelEncode($attribute_label);
            }else{
                $attribute_label = $item->getFilter()->getRequestVar();
            }
            //parsing parameters
            $params = $request->getParams();
            $label = $item->getLabel();
            $clean_label = $this->labelEncode($label);
            $url_value = $clean_label.'_'.$item->getValue();
            //unset reserved keys and set params
            $reserved_queries = $this->getReserveKeys($request);
            foreach ($reserved_queries as $key) {
                if (isset($params[$key]))
                    unset($params[$key]);
            }
            //if the parameter is empty then remove from path
            if(isset($params[$attribute_label]) && $params[$attribute_label] == $url_value){
                unset($params[$attribute_label]);
                $item->setData('selected', true);
            }else {
                $params[$attribute_label] = $url_value;
                $item->setData('selected', false);
            }
            ksort($params);
            //building urls
            if (count($params) > 0) {
                $return_url = rtrim($url_no_suffix, '/') . '/';
                if ($this->useUrlSeparator() && $this->getUrlSeparator() && !$is_search) {
                    $return_url .= $this->getUrlSeparator() . '/';
                    foreach ($params as $k => $v)
                        $return_url .= $k . '/' . $v . '/';
                } else {
                    foreach ($params as $k => $v)
                        $return_url .= $k . '/' . $v . '/';
                    if(!$is_search) $return_url .= count($params);
                }
                return $return_url;
            }else {
                return $url;
            }
        }else{
            return $item->getCategoryUrl();
        }
    }
    
    public function getCategoryRemoveItemUrl($request,$item)
    {
        $category_mode = $item->getPrapareUrl();
        if(!$category_mode)
            $category_mode = $this->getCategoryMode();

        $is_search = false;
        if($request->getModuleName() == 'catalogsearch') {
            $category_mode = 1;
            $is_search = true;
        }
        $url = '';
        if($category_mode == 1){
            list($url_no_suffix, $url) = $this->_prepareItemUrl($request, $is_search);
            $attribute_label = trim($item->getFilter()->getName());
            if ($attribute_label)
                $attribute_label = $this->labelEncode($attribute_label);
            else
                $attribute_label = $item->getFilter()->getRequestVar();
            //parsing parameters
            $params = $request->getParams();
            //remove category filter
            unset($params[$attribute_label]);
            //unset reserved keys and set params
            $reserved_queries = $this->getReserveKeys($request);
            foreach ($reserved_queries as $key) {
                if (isset($params[$key])) unset($params[$key]);
            }
            ksort($params);
            //building urls
            if (count($params) > 0) {
                $return_url = rtrim($url_no_suffix, '/') . '/';
                if ($this->useUrlSeparator() && $this->getUrlSeparator() && !$is_search) {
                    $return_url .= $this->getUrlSeparator() . '/';
                    foreach ($params as $k => $v)
                        $return_url .= $k . '/' . $v . '/';
                } else {
                    foreach ($params as $k => $v)
                        $return_url .= $k . '/' . $v . '/';
                    if(!$is_search) $return_url .= count($params);
                }
                return $return_url;
            }else{
                return $url;
            }
        }else{
            return $url;
        }
    }
    
    /**
     * 
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Catalog_Model_Layer_Filter_Item $item
     */
    public function getPriceItemUrl($request,$item){
        //check is search page
        $is_search = false;
        if($request->getModuleName() == 'catalogsearch') {
            $is_search = true;
        }
        list($url_no_suffix, $url) = $this->_prepareItemUrl($request, $is_search);
        //use attribute label rather than attribute code for better SEO but customer need to input unique name
        $attribute_label = trim($item->getFilter()->getName());
        if($attribute_label) $attribute_label = $this->labelEncode($attribute_label);
        else $attribute_label = $item->getFilter()->getRequestVar();
        //parsing parameters
        $params = $request->getParams();
        $url_value = $item->getValue(); 
        $reserved_queries = $this->getReserveKeys($request);
        //unset reserved keys and set params
        foreach($reserved_queries as $key){
            if(isset($params[$key])) unset($params[$key]);
        }
        //if the parameter is empty then remove from path
        if($url_value === '' && isset($params[$attribute_label])) unset($params[$attribute_label]);
        else $params[$attribute_label] = $url_value;
        ksort($params);
        //building urls
        if(count($params)>0){
            $return_url = rtrim($url_no_suffix,'/').'/';
            if($this->useUrlSeparator() && $this->getUrlSeparator() && !$is_search){
                $return_url .= $this->getUrlSeparator().'/';
                foreach($params as $k=>$v) $return_url .= $k.'/'.$v.'/';
            }else{
                foreach($params as $k=>$v) $return_url .= $k.'/'.$v.'/';
                if(!$is_search) $return_url .= count($params);
            }
            return $return_url;
        }else{
            return $url;
        }
    }
    
    /**
     * 
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Catalog_Model_Layer_Filter_Item $item
     */
    public function getPriceRemoveItemUrl($request,$item){
        $is_search = false;
        if($request->getModuleName() == 'catalogsearch') {
            $is_search = true;
        }
        list($url_no_suffix, $url) = $this->_prepareItemUrl($request, $is_search);
        $attribute_label = trim($item->getFilter()->getName());
        if ($attribute_label)
            $attribute_label = $this->labelEncode($attribute_label);
        else
            $attribute_label = $item->getFilter()->getRequestVar();
        //parsing parameters
        $params = $request->getParams();
        //remove
        unset($params[$attribute_label]);
        //unset reserved keys and set params
        $reserved_queries = $this->getReserveKeys($request);
        foreach ($reserved_queries as $key) {
            if (isset($params[$key]))
                unset($params[$key]);
        }
        ksort($params);
        //building urls
        if (count($params) > 0) {
            $return_url = rtrim($url_no_suffix, '/') . '/';
            if ($this->useUrlSeparator() && $this->getUrlSeparator() && !$is_search) {
                $return_url .= $this->getUrlSeparator() . '/';
                foreach ($params as $k => $v)
                    $return_url .= $k . '/' . $v . '/';
            } else {
                foreach ($params as $k => $v)
                    $return_url .= $k . '/' . $v . '/';
                if(!$is_search) $return_url .= count($params);
            }
            return $return_url;
        } else {
            return $url;
        }
    }
    
    public function labelEncode($label) {
        return urlencode( preg_replace('/[_\/]+/','-',strtolower(trim($label))) );
    }
    
    public function loadFilterSettings(){
        //load navigation additional filter settings like tooltips and others in one query
        if($this->_filter_settings == null){
            $collection = Mage::getResourceModel('bz_navigation/filter_collection');
            $filter_settings = array();
            foreach($collection as $filter){
                if($idx = $filter->getAttributeId()){
                    $filter_settings[$idx] = $filter->getData();
                }
            }
            $this->_filter_settings = $filter_settings;
        }
        return $this->_filter_settings;
    }
    
    /**
     * $block should be Mage_Catalog_Block_Layer_View or its children
     * $filter_settings varien_object of the filter settings
     * 
     */
    public function updateMeta($view_block, $filter_settings = array()){
        $state = $view_block->getChild('layer_state');
        $filters = $state->getActiveFilters();
        $title = $view_block->getLayout()->getBlock('head')->getTitle();
        $description = $view_block->getLayout()->getBlock('head')->getDescription();
        $titles = array();
        $meta_desc = array();
        foreach($filters as $f){
            $class_name = get_class($f->getFilter());
            if(stristr($class_name,'attribute') || stristr($class_name,'price') || stristr($class_name,'decimal')){
                $attr_id = $f->getFilter()->getAttributeModel()->getId();
                if(isset($filter_settings[$attr_id])){
                    $template = $filter_settings[$attr_id]['meta_description'];
                    $meta_desc[] = str_ireplace('{{options}}',$f->getLabel(),$template);
                }
            }
            $titles[] = $f->getName().' '.$f->getLabel();
        }
        if (!empty($titles)) {
            $title .= ' filtered by ' . implode(' and ', $titles);
            $view_block->getLayout()->getBlock('head')->setTitle($title);
        }
        if(!empty($meta_desc)){
            $additional_desc = implode(' ', $meta_desc);
            $view_block->getLayout()->getBlock('head')->setDescription($description.' '.$additional_desc);
        }
    }

}
