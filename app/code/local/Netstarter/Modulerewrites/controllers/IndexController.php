<?php
/**
 * Netstarter Modulerewrites Index Controller
 * @category    Netstarter
 * @package     Modulerewrites
 */

class Netstarter_Modulerewrites_IndexController extends Mage_Core_Controller_Front_Action
{

    /**
     * go to customer login page and once logged in, redirect to previous page
     */
    public function refererAction()
    {
        $bookmark = Mage::app()->getRequest()->getParam('bookmark');
        $url = '';
        if ($bookmark) {
            $url = Mage::app()->getRequest()->getServer('HTTP_REFERER').'?goto='.$bookmark;
        }
        $session=Mage::getSingleton("customer/session");
        Mage::getSingleton('customer/session')->setAfterAuthUrl($url);
        Mage::getSingleton('review/session')->setFormData($this->getRequest()->getPost())
            ->setRedirectUrl($this->_getRefererUrl());
        $this->_redirect('customer/account/login/');
    }
}
