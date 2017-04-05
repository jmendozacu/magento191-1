<?php

/**
 * @author Ben Zhang <bzhang@netstarter.com.au>
 * grid created from the multiple select and dropdown select attributes that used for filter
 */
class BZ_Navigation_Block_Adminhtml_Option_Grid extends Mage_Eav_Block_Adminhtml_Attribute_Grid_Abstract
{

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')->addVisibleFilter();
        $collection->addFieldToFilter('frontend_input', array('select', 'multiselect'));
        $collection->addFieldToFilter('is_user_defined', 1);
        $collection->addFieldToFilter(array('is_filterable_in_search','is_filterable'),array(1,1));
        $table = Mage::getResourceModel('bz_navigation/filter')->getMainTable();
        $collection->getSelect()->joinLeft(
            array('filter_table' => $table),
            'main_table.attribute_id = filter_table.attribute_id',
            array('image_width','image_height','option_limit','image_mode','display_mode')
        );
        /*$collection->getSelect()->joinLeft(
            array('op_v_table' => 'eav_attribute_option_value'),
            'op_table.option_id = op_v_table.option_id',
            array('options' => 'GROUP_CONCAT(op_v_table.value,"|",op_v_table.option_id)')
        );*/
        $this->setCollection($collection);
        //echo $collection->getSelect();exit;
        return parent::_prepareCollection();
    }

    /**
     * Prepare attributes grid columns
     * @return Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid
     */
    protected function _prepareColumns() {
        parent::_prepareColumns();
        $this->addColumnAfter('is_filterable', array(
            'header'=>Mage::helper('catalog')->__('Use in Layered Navigation'),
            'sortable'=>true,
            'index'=>'is_filterable',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Filterable (with results)'),
                '2' => Mage::helper('catalog')->__('Filterable (no results)'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ), 'is_user_defined');
        
        $this->addColumnAfter('is_filterable_in_search', array(
            'header'=>Mage::helper('catalog')->__('Use in Search Result Layered Navigation'),
            'sortable'=>true,
            'index'=>'is_filterable',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ), 'is_filterable');
        
        $this->addColumnAfter('image_width', array(
            'header'=>Mage::helper('catalog')->__('Image Width (px)'),
            'sortable'=>true,
            'index'=>'image_width',
            'align' => 'center',
        ), 'is_filterable_in_search');
        
        $this->addColumnAfter('image_height', array(
            'header'=>Mage::helper('catalog')->__('Image Height (px)'),
            'sortable'=>true,
            'index'=>'image_height',
            'align' => 'center',
        ), 'image_width');
        
        $this->addColumnAfter('option_limit', array(
            'header'=>Mage::helper('catalog')->__('Display Limit'),
            'sortable'=>true,
            'index'=>'option_limit',
            'align' => 'center',
        ), 'image_height');
        
        $this->addColumn('position', array(
            'header' => Mage::helper('bz_navigation')->__('Attribute Position Order'),
            'sortable' => true,
            'align' => 'left',
            'index' => 'position'
        ));

        return $this;
    }

}