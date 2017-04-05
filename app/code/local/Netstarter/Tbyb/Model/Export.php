<?php
/**
 * Class Netstarter_Tbyb_Model_Export
 */
class Netstarter_Tbyb_Model_Export extends Mage_Core_Model_Abstract
{
    protected $_jobId = 'GENERATE_STORE_ORDER_REPORT';
    protected $_tbybStatus = array();

    /**
     * @var array
     */
    protected $_columnMap = array(
        'item_id'       => 'Item Id',
        'order.store_id' => 'Store ID',
        'created_at'    => 'Purchase Date',
        'future_payment_date' => 'Future Payment Date',
        'order.increment_id' => 'Magento Order ID',
        'order.rd_order_code' => 'RMS Order ID',
        'sku' => 'Product SKU',
        'item_colour_ref' => 'Item Color Code',
        'price' => 'Price to be charged',
        'order.store_order_id' => 'Ordered Store ID',
        'customer_name' => 'Customer Name',
        'order.customer_email' => 'Email address',
        'status' => 'TBYB Status',
        'cancelled_at' => 'Date Cancelled'
    );

    protected function _update()
    {
        $this->exportOrders();
    }

    public function getColumnMap()
    {
        return $this->_columnMap;
    }

    /**
     * Prepare the export of items as string
     * @return string
     */
    public function getExportAll()
    {
        $items = $this->getTbybItems();
        $this->_tbybStatus = Mage::getModel('netstarter_tbyb/status')->getOptionsArray();
        return $this->prepareData($items);
    }

    /**
     * @return Netstarter_Tbyb_Model_Resource_Item_Collection
     */
    public function getTbybItems() {

        $items = Mage::getModel('netstarter_tbyb/item')->getCollection();

        $items->getSelect()
            ->joinLeft(
                array('order' => Mage::getSingleton('core/resource')->getTableName('sales/order')),
                'main_table.order_id = order.entity_id',
                array(
                    'order.store_id' => 'order.store_id',
                    'order.store_order_id' => 'order.store_order_id',
                    'order.created_at' => 'order.created_at',
                    'order.increment_id' => 'order.increment_id',
                    'order.rd_order_code' => 'order.rd_order_code',
                    'order.customer_email' => 'order.customer_email',
                )
            );
        return $items;
    }

    /**
     * @param $items try before you buy items
     */
    public function prepareData($items)
    {
        $csvLines = array();
        $columnMap = $this->getColumnMap();
        if ($columnMap) {
            $csvLines[] = implode(',', $columnMap);

            if ($items->getSize()) {
                foreach ($items as $item) {
                    $data = array();
                    foreach (array_keys($columnMap) as $col) {
                        $data[] = $this->getFormattedData($col, $item->getData($col));
                    }
                    $csvLines[] = implode(',' , $data);
                }
            }
        }
        if ($csvLines) {
            return implode("\r\n", $csvLines);
        } else {
            return "";
        }

    }

    /**
     * Format the field values
     * @param $col
     * @param $data
     * @return bool|string
     */
    public function getFormattedData($col, $data){
        switch($col) {
            case 'created_at':
                if ($data) {
                    $data = date("Y-m-d",Mage::getModel('core/date')->timestamp($data));
                }
                break;
            case 'cancelled_at':
                if ($data) {
                    $data = date("Y-m-d",Mage::getModel('core/date')->timestamp($data));
                }
                break;
            case 'future_payment_date':
                if ($data) {
                    $data = date("Y-m-d",Mage::getModel('core/date')->timestamp($data));
                }
                break;
            case 'status':
                $data = array_key_exists($data, $this->_tbybStatus) ? $this->_tbybStatus[$data] : $data;
            default:
                break;
        }
        return $data;
    }

}