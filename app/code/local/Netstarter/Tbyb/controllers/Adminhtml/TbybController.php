<?php
 
class Netstarter_Tbyb_Adminhtml_TbybController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/netstarter_tbyb');
    }
    
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales')
            ->_title($this->__('Try Before You Buy Items'));
 
        $this->renderLayout();
    }

    public function massCancelAction()
    {
        $itemIds = $this->getRequest()->getParam('item_id');
        if(!is_array($itemIds))
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('netstarter_tbyb')->__('Please select item(s).'));
        }
        else
        {
            try
            {
                $itemModel = Mage::getModel('netstarter_tbyb/item');
                
                $success = 0;
                $failure = 0;
                
                foreach ($itemIds as $itemId)
                {
                    $itemModel->load($itemId);
                    
                    if ($itemModel->getStatus() == Netstarter_Tbyb_Model_Status::STATUS_TOBECHARGED)
                    {
                        $itemModel
                                ->setStatus(Netstarter_Tbyb_Model_Status::STATUS_CANCELLED)
                                ->setUpdatedAt(time())
                                ->setCancelledAt(time())
                                ->save();
                        $success++;
                    }
                    else
                    {
                        $failure++;
                    }
                }
                
                if ($success > 0)
                {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('netstarter_tbyb')->__(
                            'Total of %d record(s) were cancelled.', $success
                        )
                    );
                }
                
                if ($failure > 0)
                {
                    Mage::getSingleton('adminhtml/session')->addWarning(
                        Mage::helper('netstarter_tbyb')->__(
                            'Total of %d record(s) were NOT cancelled because they were not in the "To Be Charged" status.', $failure
                        )
                    );
                }
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {

        $exporter = Mage::getModel('netstarter_tbyb/export');

        $content = $exporter->getExportAll();
        $fileName= 'try_before_buy_items.csv';

        $this->_prepareDownloadResponse($fileName, $content);
    }
}