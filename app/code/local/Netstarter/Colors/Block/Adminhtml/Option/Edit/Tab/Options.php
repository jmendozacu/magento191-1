<?php
/**
 * Class Options
 *
 * @author bzhang@netstarter.com.au
 */
class Netstarter_Colors_Block_Adminhtml_Option_Edit_Tab_Options extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _prepareCollection() {
        $model = Mage::registry('attribute_model');
        if($model->getId()){
            $attr_op_val_table = Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value');
            $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->addFieldToSelect(array('attribute_id','sort_order'))
                ->addFieldToFilter('attribute_id', array('eq'=>$model->getId()));
        }
        $optionCollection->getSelect()->joinLeft(
            array('op_v_table' => $attr_op_val_table),
            'op_v_table.option_id = main_table.option_id and store_id = 0',
            array('store_id','value')
        );
        $optionCollection->getSelect()->joinLeft(
            array('filter_op_table' => 'netstarter_colors_filter_option'),
            'filter_op_table.option_id = main_table.option_id and op_v_table.store_id = filter_op_table.store_id',
            array('block_id','color_code','filename','filename_one','filename_two','additional')
        );
        $optionCollection->getSelect()->order('sort_order DESC');
        $this->setCollection($optionCollection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns() {
        
        $this->addColumn('option_id', array(
            'header'=>Mage::helper('colors')->__('Option ID'),
            'sortable'=>true,
            'filter'=> false,
            'index'=>'option_id'
        ));
        
        $this->addColumn('value', array(
            'header'=>Mage::helper('colors')->__('Option Label'),
            'sortable'=>true,
            'index'=>'value'
        ));
        
        if (!Mage::app()->isSingleStoreMode()) {
            $col = Mage::getSingleton('adminhtml/system_store')->getStoreCollection();
            $stores = array(0=>'All Store View');
            foreach($col as $s){
                $stores[$s->getId()] = $s->getName();
            }
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('colors')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'options',
                'options'       => $stores,
                'sortable'      => false,
                'filter_condition_callback'
                                => array($this, '_filterStoreCondition'),
            ));
        }
        
        $this->addColumn('color_code', array(
            'header'=>Mage::helper('colors')->__('Color Code'),
            'sortable'=>true,
            'index'=>'color_code',
            //'filter_condition_callback' => array($this, '_filterStoreCondition'),
            'renderer' => 'Netstarter_Colors_Block_Adminhtml_Option_Renderer_Color'
        ));

        $this->addColumn('filename', array(
            'header'=>Mage::helper('colors')->__('Main Image'),
            'sortable'=>false,
            'filter'=> false,
            'index'=>'filename',
            'renderer' => 'Netstarter_Colors_Block_Adminhtml_Option_Renderer_Image'
        ));
        
        $this->addColumn('filename_one', array(
            'header'=>Mage::helper('colors')->__('Second Image'),
            'sortable'=>false,
            'filter'=> false,
            'index'=>'filename_one',
            'renderer' => 'Netstarter_Colors_Block_Adminhtml_Option_Renderer_Image'
        ));
        
        $this->addColumn('filename_two', array(
            'header'=>Mage::helper('colors')->__('Third Image'),
            'sortable'=>false,
            'filter'=> false,
            'index'=>'filename_two',
            'renderer' => 'Netstarter_Colors_Block_Adminhtml_Option_Renderer_Image'
        ));
        
        $this->addColumn('sort_order', array(
            'header'=>Mage::helper('colors')->__('Sort Order'),
            'sortable'=>true,
            'index'=>'sort_order'
        ));
        
        return $this;
    }
    
    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addFieldToFilter('op_v_table.store_id',array('eq'=>$value));
    }
}
