<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 11/28/13
 * Time: 9:09 AM
 *
 * This calss overridden to avoid invalidating cache on each admin save
 */ 
class Netstarter_Modulerewrites_Model_Core_Cache extends Mage_Core_Model_Cache
{
    /**
     * Save data
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param int $lifeTime
     * @return bool
     */
    public function save($data, $id, $tags = array(), $lifeTime = false)
    {
        if ($this->_disallowSave) {
            return true;
        }

        /**
         * Add global magento cache tag to all cached data exclude config cache
         */
        if (!in_array(Mage_Core_Model_Config::CACHE_TAG, $tags)) {
            $tags[] = Mage_Core_Model_App::CACHE_TAG;
        }
        return $this->getFrontend()->save((string)$data, $this->_id($id), $this->_tags($tags), $lifeTime);
    }



    /**
     * Clean cached data by specific tag
     *
     * @param   array $tags
     * @return  bool
     */
    public function clean($tags=array())
    {
        $mode = Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG;
        if (!empty($tags)) {
            if (!is_array($tags)) {
                $tags = array($tags);
            }
            foreach($tags as $t){
                if(stristr($t,'category')) return true;
            }
            $res = $this->_frontend->clean($mode, $this->_tags($tags));
        } else {
            $res = $this->_frontend->clean($mode, array(Mage_Core_Model_App::CACHE_TAG));
            $res = $res && $this->_frontend->clean($mode, array(Mage_Core_Model_Config::CACHE_TAG));
        }
        return $res;
    }
}