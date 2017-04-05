<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 12/9/13
 * Time: 2:44 PM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_Retaildirections_Block_Adminhtml_Missed_Orders_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_grid');
        $this->setUseAjax(false);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_grid_collection';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/order_collection');
        $collection->addFieldToFilter('rd_order_code', array('null' => true));
        $collection->addFieldToFilter('status', array('in' => array('processing', 'pending','complete')));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Define grid columns
     */
    protected function _prepareColumns()
    {
        $this->addColumn('increment_id',
            array(
                'header'=> Mage::helper('netstarter_retaildirections')->__('Order #'),
                'width' => 1,
                'type'  => 'text',
                'index' => 'increment_id',
            ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('netstarter_retaildirections')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));


        return parent::_prepareColumns();
    }

    /**
     * Prepare mass action options for this grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('resend_orders', array(
            'label'=> Mage::helper('sales')->__('Resend Orders'),
            'url'  => $this->getUrl('*/rdmissed/resend'),
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

//    public function getGridUrl()
//    {
//        return $this->getUrl('*/*/grid', array('_current'=>true));
//    }

}