<?php

class Netstarter_Tbyb_Block_Adminhtml_Tbyb extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'netstarter_tbyb';
        $this->_controller = 'adminhtml_tbyb';
        $this->_headerText = $this->__('Try Before You Buy Items');
        
        parent::__construct();
        
        $this->_removeButton('add');
        $this->_removeButton('back');
    }
}