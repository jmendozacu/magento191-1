<?php
class Netstarter_Tbyb_Block_Adminhtml_Tbyb_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
         
        // Set some defaults for our grid
        $this->setDefaultSort('created_at');
        $this->setId('netstarter_tbyb_tbyb_grid');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }
     
    protected function _getCollectionClass()
    {
        // This is the model we are using for the grid
        return 'netstarter_tbyb/item_collection';
    }
     
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);
         
        return parent::_prepareCollection();
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('item_id');
        $this->getMassactionBlock()->setFormFieldName('item_id');
        $this->getMassactionBlock()->addItem('delete', array
            (
                'label'=> Mage::helper('tax')->__('Cancel'),
                'url'  => $this->getUrl('*/*/massCancel', array('' => '')),
                'confirm' => Mage::helper('tax')->__('Are you sure?')
            )
        );

        return $this;
    }
     
    protected function _prepareColumns()
    {
        $this->addColumn('created_at',
            array(
                'header'=> $this->__('Date Purchased'),
                'align' => 'left',
                'width' => '50px',
                'index' => 'created_at',
                'type'  => 'datetime'
            )
        );
        
        $this->addColumn('future_payment_date',
            array(
                'header'=> $this->__('Date to be Charged'),
                'align' => 'left',
                'width' => '50px',
                'index' => 'future_payment_date',
                'type'  => 'datetime'
            )
        );
        
        $this->addColumn('increment_id',
            array(
                'header'=> $this->__('Order Increment Id'),
                'align' => 'left',
                'width' => '50px',
                'index' => 'increment_id',
            )
        );
        
        $this->addColumn('customer_name',
            array(
                'header'=> $this->__('Customer Name'),
                'align' => 'left',
                'width' => '50px',
                'index' => 'customer_name',
            )
        );
        
        $this->addColumn('sku',
            array(
                'header'=> $this->__('Product SKU'),
                'align' => 'left',
                'width' => '50px',
                'index' => 'sku',
            )
        );
        
        $this->addColumn('price',
            array(
                'header'=> $this->__('Price to be Charged'),
                'align' => 'left',
                'width' => '50px',
                'index' => 'price',
                'type'  => 'currency',
                'currency' => 'currency_code',
            )
        );
        
        
        $this->addColumn('status',
            array(
                'header'=> $this->__('Status'),
                'align' => 'left',
                'width' => '50px',
                'index' => 'status',
                'type' => 'options',
                'options' => Mage::getModel('netstarter_tbyb/status')->getOptionsArray(),
                'renderer' => 'Netstarter_Tbyb_Block_Adminhtml_Tbyb_Grid_Column_Renderer_Status'
            )
        );


        //Export the table as CSV
        $this->addExportType('*/*/exportCsv', Mage::helper('tax')->__('CSV'));

        return parent::_prepareColumns();
    }
     
    public function getRowUrl($row)
    {
        return false;
    }
}