 <?php
/**
 * Process parameters and imports the CSV.
 * Generates shipping and tracks informatino for processed orders.
 *
 * @category  Netstarter
 * @package   Netstarter_Startrack
 * 
 * Class Netstarter_Startrack_Adminhtml_ImportcsvController
 */
class Netstarter_Startrack_Adminhtml_ImportcsvController extends Mage_Adminhtml_Controller_Action
{
    const TITLE_TRACKING = 'Startrack';
    const NOTIFY = true;
    
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
     * Process to generate shippings for all items of an order.
     * This retrieves all items of an order.
     * 
     * @param Mage_Sales_Model_Order $order
     */
    protected function _isAllowed()
    {
        return true;
    }
    protected function _getItemQtys($order)
    {
        $itemsArray = array();
        
        foreach ($order->getAllItems() as $item)
        {
            $itemsArray[intval($item->getItemId())] = intval($item->getQtyOrdered() - ($item->getQtyCanceled() + $item->getQtyRefunded()));
        }
        
        return $itemsArray;
    }
    
    /**
     * Generate shipping object based on the order
     * 
     * @param string $incrementId
     * @param string $trackingNumber
     */ 
    protected function _initShipment($incrementId, $trackingNumber,$trackingTitle)
    {
        $shipment = false;

        if ($incrementId) {
            $order      = Mage::getModel('sales/order')->loadByIncrementId($incrementId);

            /**
             * Check order existing
             */
            if (!$order->getId()) {
                Mage::throwException($this->__('The order no longer exists.'));
                return false;
            }

            /**
             * Check shipment is available to create separate from invoice
             */
            if ($order->getForcedDoShipmentWithInvoice()) {
                Mage::throwException($this->__('Cannot do shipment for the order separately from invoice.'));
                return false;
            }
            
            /**
             * Check shipment create availability
             */
            if (!$order->canShip()) {
                Mage::throwException($this->__('Cannot do shipment for the order.'));
                return false;
            }
            
            $savedQtys = $this->_getItemQtys($order);
            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);

            if (empty($trackingNumber)) {
                Mage::throwException($this->__('Tracking number cannot be empty.'));
            }
            
            $data = array(
                'carrier' => 'netstarter_startrack',
                'title' => $trackingTitle,
                'number' => $trackingNumber,
            );
            
            $track = Mage::getModel('sales/order_shipment_track')
                ->addData($data);
            $shipment->addTrack($track);
        }

        return $shipment;
    }
    
    /**
     * Save shipment and order as a transaction
     * 
     * @param Mage_Sales_Model_Order_Shipment $shipment
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        return $this;
    }

    public  function  changeStatus($incrementId, $trackingNumber,$trackingTitle)
    {
        $status=false;
        if ($incrementId) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);

            /**
             * Check order existing
             */
            if (!$order->getId()) {
                Mage::throwException($this->__('The order no longer exists.'));
                return false;
            }

            $order->setData('state', "Processing");
            $order->setStatus("dispatched");
            $history = $order->addStatusHistoryComment($trackingNumber, false);
            $history->setIsCustomerNotified(false);
            $order->save();
        }
    }

    /**
     * Save shipment and order as a transaction
     * 
     */
    public function importAction()
    {
        $data = $this->getRequest()->getPost();
        
        $uploader = new Varien_File_Uploader('csv_file');

        $uploader->setAllowedExtensions(array('csv'));
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        
        $result = $uploader->save(Mage::getBaseDir('var') . '/import/');
        
        $io = new Varien_Io_File();
        $io->open(array('path' => $result['path']));
        $io->streamOpen($result['name'], "r");
        
        
//        $firstLine  = true;
        $line       = 0;
        
        $successArray   = array();
        $errorArray     = array();
        
        $error      = false;
        $success    = false;
        
        while ($buffer = $io->streamReadCsv())
        {
            $line++;
            
//            if ($firstLine)
//            {
//                $firstLine = false;
//                continue;
//            }
            
            if (!(strlen($buffer[0]) > 0))
            {
                continue;
            }
            
            try
            {
                if (! isset($buffer[2]))
                    $buffer[2] = self::TITLE_TRACKING;
                if ($buffer[2]=="ClickNCollect")
                {
                    $this->changeStatus($buffer[0], $buffer[1], $buffer[2]);
                }
                else {
                    $shipment = $this->_initShipment($buffer[0], $buffer[1], $buffer[2]);

                    if ($shipment == false) {
                        continue;
                    }

                    $shipment->register();

                    if (self::NOTIFY) {
                        $shipment->getOrder()->setCustomerNoteNotify(true);
                        $comment = '';
                    }

                    $this->_saveShipment($shipment);

                    if (self::NOTIFY) {
                        $shipment->sendEmail(true, $comment);
                    }
                }
            }
            catch (Exception $e)
            {
                $error = true;
                
                array_push($errorArray,$buffer[0]);
                $this->_getSession()->addError($e->getMessage() . " - on line $line.");
                
                continue;
            }
            
            $success = true;
            array_push($successArray,$buffer[0]);
        }
        
        $io->streamClose();     
        $io->rm($result['name']);
        
        if ($error)
        {
            $this->_getSession()->addError("File imported with errors: " . implode(", ", $errorArray));
        }
        
        if ($success)
        {
            $this->_getSession()->addSuccess("File imported with successes: " . implode(", ", $successArray));
        }

        $this->_redirect('*/*/index');
    }
}
