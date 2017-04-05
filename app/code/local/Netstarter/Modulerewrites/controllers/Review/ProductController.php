<?php
/**
 * Review controller
 *
 * @category   Netstarter
 * @package    Netstarter_Modulerewrites_Review
 * @author     Netstarer
 */
require_once 'Mage'.DS.'Review'.DS.'controllers'.DS.'ProductController.php';
class Netstarter_Modulerewrites_Review_ProductController extends Mage_Review_ProductController
{
    public function postAction()
    {
        // Redirect if request is not Ajax
        if (!$this->getRequest()->isAjax()) {
            $session    = Mage::getSingleton('core/session');
            $session->addError($this->__('Unable to post the review.'));

            if ($redirectUrl = Mage::getSingleton('review/session')->getRedirectUrl(true)) {
                $this->_redirectUrl($redirectUrl);
                return;
            }
            $this->_redirectReferer();
            return;
        }

        $returnData = array();

        if ($data = Mage::getSingleton('review/session')->getFormData(true)) {
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data   = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', array());
        }

        if (($product = $this->_initProduct()) && !empty($data)) {
            /* @var $session Mage_Core_Model_Session */
            $review     = Mage::getModel('review/review')->setData($data);
            /* @var $review Mage_Review_Model_Review */

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($product->getId())
                        ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->setStores(array(Mage::app()->getStore()->getId()))
                        ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        Mage::getModel('rating/rating')
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->addOptionVote($optionId, $product->getId());
                    }

                    $review->aggregate();
                    $response['status'] = 'success';
                    $response['msg'] = $this->__("<h2>Thanks!</h2><h3>We really appreciate your feedback. Check back in the next couple of days to see your review.</h3>");
                }
                catch (Exception $e) {
                    $response['status'] = 'error';
                    $response['msg'] = $this->__('Unable to post the review.');
                }
            }
            else {
                $session->setFormData($data);
                if (is_array($validate)) {
                    $errorMessage = '';
                    foreach ($validate as $errorMessage) {
                        $errorMessage .= $errorMessage;
                    }
                    $response['status'] = 'error';
                    $response['msg'] = $errorMessage;
                }
                else {
                    $response['status'] = 'error';
                    $response['msg'] = $this->__('Unable to post the review.');
                }
            }
        }

        echo Mage::helper('core')->jsonEncode($response);
        die();
    }
}
