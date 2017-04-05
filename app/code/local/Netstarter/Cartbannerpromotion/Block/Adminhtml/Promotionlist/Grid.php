<?php
/**
 * Created by PhpStorm.
 * User: Tuan
 * Date: 3/21/14
 * Time: 7:20 PM
 */

class Netstarter_Cartbannerpromotion_Block_Adminhtml_Promotionlist_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct()
    {
        parent::__construct();
        $this->setId('promotion_id');
        $this->setDefaultSort('promotion_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('cartbannerpromotion/promotionlist')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('promotion_id', array(
            'header'    => Mage::helper('cartbannerpromotion')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'promotion_id',
        ));

        $this->addColumn('promotion_name', array(
            'header'    => Mage::helper('cartbannerpromotion')->__('Promotion Name'),
            'align'     =>'left',
            'index'     => 'promotion_name',
        ));

        $this->addColumn('product_id', array(
            'header'    => Mage::helper('cartbannerpromotion')->__('Product ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'product_id',
        ));

        $this->addColumn('promotion_text', array(
            'header'    => Mage::helper('cartbannerpromotion')->__('Promotion Text'),
            'align'     =>'left',
            'index'     => 'promotion_text',
        ));

        $this->addColumn('promotion_start', array(
            'header'    => Mage::helper('cartbannerpromotion')->__('Promotion Start'),
            'width'     => '100px',
            'index'     => 'promotion_start',
        ));

        $this->addColumn('promotion_end', array(
            'header'    => Mage::helper('cartbannerpromotion')->__('Promotion End'),
            'width'     => '100px',
            'index'     => 'promotion_end',
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('cartbannerpromotion')->__('Status'),
            'width'     => '100px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(1=>"Active",0=>"Inactive")
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
} 