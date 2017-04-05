<?php
/**
 * Created by PhpStorm.
 * User: Tuan
 * Date: 3/21/14
 * Time: 7:18 PM
 */

class Netstarter_Cartbannerpromotion_Block_Adminhtml_Promotionlist extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct()
    {

        $this->_controller = 'adminhtml_promotionlist';
        $this->_blockGroup = 'cartbannerpromotion';
        $this->_headerText = Mage::helper('cartbannerpromotion')->__('Cart Promotion List Manager');
        parent::__construct();
        $this->_removeButton('add promotion');
    }
} 