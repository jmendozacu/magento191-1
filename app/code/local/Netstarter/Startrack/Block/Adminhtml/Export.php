<?php

/**
 * Class Netstarter_Startrack_Block_Adminhtml_Export
 *
 * @category  Netstarter
 * @package   Netstarter_Startrack
 *
 * Default backend form container.
 *
 */
class Netstarter_Startrack_Block_Adminhtml_Export extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml';
        $this->_mode = 'export';
        $this->_blockGroup = 'netstarter_startrack';
        $this->_headerText = Mage::helper('netstarter_startrack')->__('Export Startrack CSV');

        parent::__construct();

        $this->_removeButton('back');
        $this->_removeButton('reset');
        $this->_removeButton('save');

        $this->_addButton('save', array(
            'label'     => Mage::helper('netstarter_startrack')->__('Export'),
            'onclick'   => 'editForm.submit();',
            'class'     => 'save',
        ),-1,5);
    }
}