<?php
 
class Netstarter_Location_Block_Adminhtml_Postcodes_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {

        parent::__construct();
        $this->setId('postcodesGrid');
        // This is the primary key of the database
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }
 
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('location/postcode_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => $this->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'id',
        ));

        $this->addColumn('countrycode', array(
            'header'    => $this->__('Country Code'),
            'width'     => '100px',
            'align'     =>'left',
            'index'     =>'countrycode',
        ));

        $this->addColumn('postcode', array(
            'header'    => $this->__('Postcode'),
            'width'     => '100px',
            'align'     =>'left',
            'index'     =>'postcode',
        ));

        $this->addColumn('suburb', array(
            'header'    => $this->__('Suburb'),
            'align'     =>'right',
            'width'     => '100px',
            'index'     => 'suburb',
        ));

        $this->addColumn('state', array(
            'header'    => $this->__('State'),
            'align'     => 'left',
            'width'     => '300px',
            'index'     => 'state',
        ));
        $this->addColumn('statecode',array(
           'header' => $this->__('State Code'),
            'align' => 'left',
            'width' => '300px',
            'index' => 'statecode'
        ));
        $this->addColumn('city',array(
           'header' => $this->__('City'),
            'align' => 'left',
            'index' => 'city'
        ));

        return parent::_prepareColumns();
    }
 
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
 
}