<?php
/**
 * @author: Ben Zhang <bzhang@netstarter.com.au>
 */
class BZ_Navigation_Block_Adminhtml_Option extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Define controller, block and labels
     */
    public function __construct() {
        parent::__construct();
        $this->_blockGroup = 'bz_navigation';
        $this->_controller = 'adminhtml_option';
        $this->_headerText = Mage::helper('bz_navigation')->__('Manage Filter Attribute Options');
        $this->_removeButton('add');
    }

}