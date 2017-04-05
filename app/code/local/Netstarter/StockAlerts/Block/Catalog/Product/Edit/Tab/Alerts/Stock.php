<?php
class Netstarter_StockAlerts_Block_Catalog_Product_Edit_Tab_Alerts_Stock extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Alerts_Stock
{

    protected function _prepareCollection()
    {

        $productId = $this->getRequest()->getParam('id');
        $websiteId = 0;
        if ($store = $this->getRequest()->getParam('store')) {
            $websiteId = Mage::app()->getStore($store)->getWebsiteId();
        }
        if (Mage::helper('catalog')->isModuleEnabled('Mage_ProductAlert')) {
            $collection = Mage::getModel('productalert/stock')->getCollection()
                ->joinCustomer($productId, $websiteId);
            $this->setCollection($collection);
        }
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('firstname', array(
            'header'    => Mage::helper('catalog')->__('First Name'),
            'index'     => 'firstname',
            'renderer'  => 'Netstarter_StockAlerts_Block_Catalog_Product_Edit_Tab_Alerts_Renderer_Name'
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('catalog')->__('Last Name'),
            'index'     => 'lastname',
            'renderer'  => 'Netstarter_StockAlerts_Block_Catalog_Product_Edit_Tab_Alerts_Renderer_Name'
        ));

        $this->addColumn('customer_type', array(
            'header'    => Mage::helper('catalog')->__('Customer Type'),
            'index'     => 'guest_customer',
            'renderer'  => 'Netstarter_StockAlerts_Block_Catalog_Product_Edit_Tab_Alerts_Renderer_Customer',
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('catalog')->__('Email'),
            'index'     => 'email',
            'renderer'  => 'Netstarter_StockAlerts_Block_Catalog_Product_Edit_Tab_Alerts_Renderer_Email',
        ));

        $this->addColumn('add_date', array(
            'header'    => Mage::helper('catalog')->__('Date Subscribed'),
            'index'     => 'add_date',
            'type'      => 'date'
        ));

        $this->addColumn('send_date', array(
            'header'    => Mage::helper('catalog')->__('Last Notification'),
            'index'     => 'send_date',
            'type'      => 'date'
        ));

        $this->addColumn('send_count', array(
            'header'    => Mage::helper('catalog')->__('Send Count'),
            'index'     => 'send_count',
        ));

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
}