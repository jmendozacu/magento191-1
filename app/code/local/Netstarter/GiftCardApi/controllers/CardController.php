<?php
/**
 * @author Prasad
 *
 * Class Netstarter_GiftCardApi_AjaxController
 */
class Netstarter_GiftCardApi_CardController extends Mage_Core_Controller_Front_Action
{

    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }

    protected function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))) {
            $this->_ajaxRedirectResponse();
            return true;
        }

        return false;
    }


    /**
     * Remove Gift Card from current quote in cart AJAX
     *
     */

    public function removeAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if($this->getRequest()->isPost()){

            $isAjax = $this->getRequest()->getParam('isAjax');
            if($isAjax){

                $gid = $this->getRequest()->getParam('gid');
                $hasfree = $this->getRequest()->getParam('hasfree');

                if($gid){

                    $totalBlock = $this->getLayout()->createBlock('checkout/cart_totals');
                    $totalBlock->setTemplate('giftcardapi/onepage/payment/totals.phtml');

                    try{

                        $returnVal = Mage::getModel('giftcardapi/process')->removeGiftCard($gid);

                        $action = '';
                        if((!$returnVal && !$hasfree) || ($returnVal && $hasfree)){

                            $layout = $this->getLayout();
                            $update = $layout->getUpdate();
                            $update->addHandle('checkout_onepage_paymentmethod');
                            $this->loadLayoutUpdates();
                            $this->generateLayoutXml()->generateLayoutBlocks();

                            $action =    $layout->getBlock('root')->toHtml();
                        }

                        $totalBlock->setSuccessMessage("Gift card Removed Successfully");

                        echo json_encode(array('msg'=>'SUCCESS','cnt'=> $totalBlock->toHtml(),'action'=> $action));

                    }catch (Netstarter_GiftCardApi_Exception $e){

                        $totalBlock->setErrorMessage($e->getMessage());

                        echo json_encode(array('msg'=>'ERROR','cnt'=> $totalBlock->toHtml(),'action'=> null));
                    }catch (Exception $e){

                        $totalBlock->setErrorMessage($e->getMessage());

                        echo json_encode(array('msg'=>'ERROR','cnt'=>$totalBlock->toHtml(),'action'=> null));
                    }
                }
            }
        }

        exit;
    }


    /**
     * Add Gift Card to current quote in cart
     *
     */
    public function applyincartAction()
    {
        $data = $this->getRequest()->getPost();
        if (isset($data['giftcard_code'])) {
            $cardNumber = $data['giftcard_code'];
            $pinNumber = $data['giftcard-pin'];

            try {

                $cardProcessor = Mage::getModel('giftcardapi/process')->processGiftCard($cardNumber, $pinNumber);

                if($cardProcessor->hasValidCard()){

                    $returnVal = $cardProcessor->apply();

                    Mage::getSingleton('checkout/session')->addSuccess(
                        $this->__('Gift Card "%s" was added.', Mage::helper('core')->htmlEscape($cardNumber))
                    );

                }else{

                    Mage::getSingleton('checkout/session')->addError("Invalid Gift card #{$cardNumber}");
                }

            } catch (Mage_Core_Exception $e) {
                Mage::dispatchEvent('enterprise_giftcardaccount_add', array('status' => 'fail', 'code' => $cardNumber));
                Mage::getSingleton('checkout/session')->addError(
                    $e->getMessage()
                );
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addException($e, $this->__('Cannot apply gift card.'));
            }
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * Add Gift Card to current quote in cart AJAX
     *
     */
    public function applyAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if($this->getRequest()->isPost()){

            $isAjax = $this->getRequest()->getParam('isAjax');
            if($isAjax){

                $cardNumber = $this->getRequest()->getParam('num');
                $pinNumber = $this->getRequest()->getParam('pin');
                $hasfree = $this->getRequest()->getParam('hasfree');


                if($cardNumber){

                    try{

                        $totalBlock = $this->getLayout()->createBlock('checkout/cart_totals');
                        $totalBlock->setTemplate('giftcardapi/onepage/payment/totals.phtml');
                        $cardProcessor = Mage::getModel('giftcardapi/process')->processGiftCard($cardNumber, $pinNumber);

                        if($cardProcessor->hasValidCard()){

                            $returnVal = $cardProcessor->apply();

                            $action = '';
                            if((!$returnVal && !$hasfree) || ($returnVal && $hasfree)){

                                $layout = $this->getLayout();
                                $update = $layout->getUpdate();
                                $update->addHandle('checkout_onepage_paymentmethod');
                                $this->loadLayoutUpdates();
                                $this->generateLayoutXml()->generateLayoutBlocks();

                                $action =    $layout->getBlock('root')->toHtml();
                            }

                            $totalBlock->setSuccessMessage("Gift card #{$cardNumber} Applied Successfully");

                            echo json_encode(array('msg'=>"SUCCESS",
                                'cnt'=> $totalBlock->toHtml(),
                                'action'=>$action));

                        }else{

                            $totalBlock->setErrorMessage("Invalid Gift card #{$cardNumber}");

                            echo json_encode(array('msg'=>'ERROR','cnt'=> $totalBlock->toHtml(),'action'=> null));
                        }

                    }catch (Netstarter_GiftCardApi_Exception $e){

                        $totalBlock->setErrorMessage($e->getMessage());

                        echo json_encode(array('msg'=>'ERROR','cnt'=> $totalBlock->toHtml(),'action'=> null));
                    }catch (Exception $e){

                        $totalBlock->setErrorMessage($e->getMessage());

                        echo json_encode(array('msg'=>'ERROR','cnt'=>$totalBlock->toHtml(),'action'=> null));
                    }
                }
            }
        }
        exit;
    }
}
