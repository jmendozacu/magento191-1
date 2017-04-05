<?php
/**
 * Exclude cache
 *
 * @author Prasad
 */
class Netstarter_Location_Model_Container_List extends Enterprise_PageCache_Model_Container_Abstract
{

    protected function _getCacheId()
    {

        return 'STORE_LOCATOR_LIST_' .$this->_placeholder->getAttribute('name').md5($this->_placeholder->getAttribute('cache_id'));
    }

    protected function _renderBlock()
    {
        $placeholder = $this->_placeholder;
        $block = $placeholder->getAttribute('block');

        $block = new $block;
        $block->setTemplate($placeholder->getAttribute('template'));

        $block->setLayout(Mage::app()->getLayout());
        return $block->toHtml();
    }

    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false;
    }
}

?>
