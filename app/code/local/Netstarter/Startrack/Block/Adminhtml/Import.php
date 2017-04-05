<?php

/**
 * Class Netstarter_Startrack_Block_Adminhtml_Import
 *
 * @category  Netstarter
 * @package   Netstarter_Startrack
 *
 * Default backend form container.
 *
 */
class Netstarter_Startrack_Block_Adminhtml_Import extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml';
        $this->_mode = 'import';
        $this->_blockGroup = 'netstarter_startrack';
        $this->_headerText = Mage::helper('netstarter_startrack')->__('Import Startrack CSV');

        parent::__construct();

        $this->_removeButton('back');
        $this->_removeButton('reset');
        $this->_removeButton('save');

        $this->_addButton('save', array(
            'label'     => Mage::helper('netstarter_startrack')->__('Import'),
            'onclick'   => 'editForm.submit();',
            'class'     => 'save',
        ),-1,5);
    }
}