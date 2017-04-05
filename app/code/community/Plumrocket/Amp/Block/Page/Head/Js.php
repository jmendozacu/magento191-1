<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Amp
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

class Plumrocket_Amp_Block_Page_Head_Js extends Mage_Core_Block_Text
{
    protected $_js = array();

    public function addJs($src, $type, $element = null) {
        $this->_js[$type]['src'] = $src;
        $this->_js[$type]['element'] = $element ? $element : 'element';
        return $this;
    }

    protected function _toHtml()
    {
        $this->setText('');
        foreach ($this->_js as $type => $data) {
            $this->addText(
                '<script async '. ($type ? 'custom-' . $data['element'] . '="' . $type . '"' : '') . ' src="' . $data['src'] . '"></script>'
            );
        }

        $this->addText('<script async src="https://cdn.ampproject.org/v0.js"></script>');
        return parent::_toHtml();
    }
}