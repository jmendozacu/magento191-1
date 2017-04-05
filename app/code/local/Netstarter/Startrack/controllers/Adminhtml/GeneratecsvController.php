<?php
/**
 * Process parameters and generates the CSV.
 *
 * @category  Netstarter
 * @package   Netstarter_Startrack
 * 
 * Class Netstarter_Startrack_Adminhtml_GeneratecsvController
 */
class Netstarter_Startrack_Adminhtml_GeneratecsvController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    protected function _isAllowed()
    {
        return true;
    }
    public function exportAction()
    {
        $data = $this->getRequest()->getPost();
        
        $data = $this->_filterDates($data, array('from_date', 'to_date'));
        
        $data['from_date'] = $data['from_date'] . " 00:00:00";
        $data['to_date'] = $data['to_date'] . " 23:59:59";
        
        $now = Mage::getModel('core/date')->timestamp(strtotime($data['from_date']));
        $fromDateUTC = date('Y-m-d H:i:s', $now);
        
        $now = Mage::getModel('core/date')->timestamp(strtotime($data['to_date']));
        $toDateUTC = date('Y-m-d H:i:s', $now);

        $sales = Mage::getModel('sales/order')->getCollection();
        $sales->clear();
        
        // gather information
        $sales
            ->getSelect()
            ->joinLeft(array('sa' => $sales->getTable('sales/order_address')), 'main_table.entity_id = sa.parent_id')
            ->joinLeft(array('co' => $sales->getTable('sales/order_status_history')), "main_table.entity_id = co.parent_id AND co.status = 'pending' and co.entity_name IS null")
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(
                array(
                    "main_table.created_at as Date",
                    "main_table.increment_id as Invoice Number",
                    new Zend_Db_Expr("'1' as `Quantity`"), // Changed as per "BNT-1062"
                    new Zend_Db_Expr("'A' as `Service`"),
                    "CONCAT(sa.firstname, ' ', sa.lastname) as Contact Name",
                    "sa.company as Company Name",
                    "REPLACE(REPLACE(REPLACE(sa.street, CONCAT(CHAR(13), CHAR(10)), ' '), CHAR(10), ' '), CHAR(13), ' ') as Address Line 1",
                    new Zend_Db_Expr("'' as `Address Line 2`"),
                    "sa.city as Suburb",
                    "sa.postcode as Postcode",
                    "CONCAT('Ph: ', sa.telephone) as Description",
                    new Zend_Db_Expr("'' as `Insurance Value`"),
                    new Zend_Db_Expr("'' as `Insurance Level`"),
                    "co.comment as `Delivery Instructions`",
                    new Zend_Db_Expr("'' as `Payment Terms Code`"),
                    new Zend_Db_Expr("'2979583' as `Charge Account Number`"),
                    new Zend_Db_Expr("'' as `Reference One Field`"),
                    new Zend_Db_Expr("'1' as `Weight`"),
                    new Zend_Db_Expr("'' as `Volume`"),
                    new Zend_Db_Expr("'' as `Hand Rate`"),
                    new Zend_Db_Expr("'' as `Shipper`"),
                    new Zend_Db_Expr("'' as `Version`"),
                    new Zend_Db_Expr("'' as `Receiver Code`"),
                    new Zend_Db_Expr("'' as `Email Address`"),
                    new Zend_Db_Expr("'' as `Return Label Required`"),
                    new Zend_Db_Expr("'0' as `Consignment Number`"),
                    new Zend_Db_Expr("'' as `Address Line 3`"),
                    new Zend_Db_Expr("'' as `Dangerous Goods Type`"),
                )
            )
            ->where("main_table.shipping_method IN('netstarter_startrack_netstarter_startrack', 'freeshipping_freeshipping')")
            ->where("main_table.status = 'processing'")
            ->where("sa.address_type = 'shipping'")
            ->where("sa.country_id = 'AU'")
            ->where("main_table.created_at >= '$fromDateUTC' AND main_table.created_at <= '$toDateUTC'");
        
        // generate files
        $io = new Varien_Io_File();

        $path = Mage::getBaseDir('var') . DS . 'export' . DS;
        $name = md5(microtime());
        $file = $path . DS . $name . '.csv';

        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);

        foreach ($sales as $item)
        {
            $now = Mage::getModel('core/date')->timestamp(strtotime($item->getData("Date")));
            $dbDateUTC = date('Y-m-d', $now);
            $item->setData("Date", $dbDateUTC);
            
            $io->streamWriteCsv($item->getData());
        }

        $io->streamUnlock();
        $io->streamClose();

        $response = array(
            'type'  => 'filename',
            'value' => $file,
            'rm'    => true // can delete file after use
        );
        
        // respond as a browser downloads
        $this->_prepareDownloadResponse('startrack_'.date('Y_m_d_H_i_s').'.csv', $response);
    }
}