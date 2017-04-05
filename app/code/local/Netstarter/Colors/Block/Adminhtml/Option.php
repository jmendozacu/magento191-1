<?php
/**
 * @author: Ben Zhang <bzhang@netstarter.com.au>
 */
class Netstarter_Colors_Block_Adminhtml_Option extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Define controller, block and labels
     */
    public function __construct() {
        parent::__construct();
        $this->_blockGroup = 'colors';
        $this->_controller = 'adminhtml_option';
        $this->_headerText = Mage::helper('colors')->__('Manage Filter Attribute Options');
        $this->_removeButton('add');
    }

}