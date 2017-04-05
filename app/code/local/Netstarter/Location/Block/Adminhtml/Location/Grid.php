<?php
 
class Netstarter_Location_Block_Adminhtml_Location_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {

        parent::__construct();
        $this->setId('locationGrid');
        // This is the primary key of the database
        $this->setDefaultSort('location_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }
 
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('location/main')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {
        $this->addColumn('location_id', array(
            'header'    => $this->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'location_id',
        ));


        $this->addColumn('name', array(
            'header'    => $this->__('Name'),
            'width'     => '300px',
            'align'     =>'left',
            'index'     =>'name',
        ));

        $this->addColumn('identifier', array(
            'header'    => $this->__('Identifier'),
            'align'     =>'right',
            'width'     => '100px',
            'index'     => 'identifier',
        ));

        $this->addColumn('email', array(
            'header'    => $this->__('Email'),
            'align'     => 'left',
            'width'     => '300px',
            'index'     => 'email',
        ));
        $this->addColumn('phone',array(
           'header' => $this->__('Phone'),
            'align' => 'left',
            'width' => '300px',
            'index' => 'phone'
        ));
        $this->addColumn('phone',array(
           'header' => $this->__('Phone'),
            'align' => 'left',
            'index' => 'phone'
        ));


//        $this->addColumn('store_id', array(
//            'header'        => $this->__('Store View'),
//            'index'         => 'store_id',
//            'type'          => 'store',
//            'store_all'     => true,
//            'store_view'    => true,
//            'sortable'      => false,
//            'filter_condition_callback'
//            => array($this, '_filterStoreCondition'),
//        ));


        $this->addColumn('active', array(
            'header'    => Mage::helper('cms')->__('Status'),
            'index'     => 'active',
            'type'      => 'options',
            'options'   => array(
                0 => $this->__('Inactive'),
                1 => $this->__('Active')
            ),
        ));

        return parent::_prepareColumns();
    }
 
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
 
 
}