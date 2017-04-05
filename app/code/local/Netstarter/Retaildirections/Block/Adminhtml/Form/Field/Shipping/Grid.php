<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 9/26/13
 * Time: 1:27 AM
 * To change this template use File | Settings | File Templates.
 */

class Netstarter_Retaildirections_Block_Adminhtml_Form_Field_Shipping_Grid extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * @var Mage_CatalogInventory_Block_Adminhtml_Form_Field_Customergroup
     */
    protected $_groupRenderer;

    /**
     * Retrieve group column renderer
     *
     * @return Mage_CatalogInventory_Block_Adminhtml_Form_Field_Customergroup
     */
    protected function _getGroupRenderer()
    {
        if (!$this->_groupRenderer) {
            $this->_groupRenderer = $this->getLayout()->createBlock(
                'netstarter_retaildirections/adminhtml_form_field_shipping_renderer', '',
                array('is_render_to_js_template' => true)
            );
            $this->_groupRenderer->setClass('shipping_group_select');
            $this->_groupRenderer->setExtraParams('style="width:120px"');
        }
        return $this->_groupRenderer;
    }

    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn('shipping_code', array(
            'label' => $this->__('Shipping Method'),
            'renderer' => $this->_getGroupRenderer(),
        ));
        $this->addColumn('rd_code', array(
            'label' => $this->__('RD Code'),
            'style' => 'width:100px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = $this->__('Add Shipping Method');
    }

    /**
     * Prepare existing row data object
     *
     * @param Varien_Object
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getGroupRenderer()->calcOptionHash($row->getData('shipping_code')),
            'selected="selected"'
        );
    }
}
