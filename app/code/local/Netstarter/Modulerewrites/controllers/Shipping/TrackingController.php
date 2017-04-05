<?php
/**
 * Sales orders controller
 *
 * @category   Netstarter
 * @package    Netstarter_Modulerewrites
 */
require_once Mage::getModuleDir('controllers', 'Mage_Shipping').DS.'TrackingController.php';
class Netstarter_Modulerewrites_Shipping_TrackingController extends Mage_Shipping_TrackingController
{
    protected $_noTrackingTemplateBlock = 'shipping_no_tracking';
    /**
     * Popup action
     * overridden to add custom functionality
     * Shows tracking info if it's present, otherwise redirects to 404
     */
    public function popupAction()
    {
        $shippingInfoModel = Mage::getModel('shipping/info')->loadByHash($this->getRequest()->getParam('hash'));
        Mage::register('current_shipping_info', $shippingInfoModel);
        if (count($shippingInfoModel->getTrackingInfo()) == 0) {
            $this->_forward('notracking');
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Action when the order is in processing state, no tracking code information is available
     */
    public function notrackingAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
}
