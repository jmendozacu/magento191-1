<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Container
 *
 * @author Administrator
 */
class Netstarter_Productwidget_Model_Container_Widget extends Enterprise_PageCache_Model_Container_Abstract
{

    protected function _getCacheId()
    {

        return 'PRO_WIDGET_' .$this->_placeholder->getAttribute('name').md5($this->_placeholder->getAttribute('cache_id'));
    }

    protected function _renderBlock()
    {

        $placeholder = $this->_placeholder;
        $block = $placeholder->getAttribute('block');

        $parameters = array('template', 'display_mode', 'product_ids', 'display_title');

        $block = new $block;

        foreach ($parameters as $parameter) {

            $value = $placeholder->getAttribute($parameter);
            $block->setData($parameter, $value);
        }

        $block->setLayout(Mage::app()->getLayout());

        return $block->toHtml();
    }

    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false;
    }
}

