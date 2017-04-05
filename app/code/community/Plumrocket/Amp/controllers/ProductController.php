<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Amp
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

/**
 * require parent controller class
 */
require_once(Mage::getModuleDir('controllers','Mage_Review') . DS . 'ProductController.php');

class Plumrocket_Amp_ProductController extends Mage_Review_ProductController
{
    /**
     * Rewrite parent method
     */
    public function postAction()
    {

        if (!$this->_validateFormKey()) {
            $data = array(
                'result'=>'error',
                'message'=>$this->__('Invalid Form Key'),
            );
            $this->_sendResponseByData($data);
            return;
        }

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
            $session = Mage::getSingleton('core/session');
            /* @var $session Mage_Core_Model_Session */
            $review = Mage::getModel('review/review')->setData($this->_cropReviewData($data));
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
                    $data = array(
                        'result'=>'success',
                        'message'=>$this->__('Your review has been accepted for moderation.'),
                    );
                    $this->_sendResponseByData($data);
                    return;
                }
                catch (Exception $e) {
                    $data = array(
                        'result'=>'error',
                        'message'=>$this->__('Unable to post the review.'),
                    );
                    $this->_sendResponseByData($data);
                    return;
                }
            } else {
                if (is_array($validate)) {
                    $data = array(
                        'result'=>'error',
                        'message'=> implode(', ', $validate),
                    );
                    $this->_sendResponseByData($data);
                    return;
                }
                else {
                    $data = array(
                        'result'=>'error',
                        'message'=>$this->__('Unable to post the review.'),
                    );
                    $this->_sendResponseByData($data);
                    return;
                }
            }
        }

        $data = array(
            'result'=>'error',
            'message'=>$this->__('Invalid Review Data.'),
        );
        $this->_sendResponseByData($data);
        return;
    }

    protected function _sendResponseByData($data)
    {
        $response = $this->getResponse();
        $response->clearHeaders()
            ->setHeader('Content-type', 'application/json')
            ->setHeader(
                'AMP-Access-Control-Allow-Source-Origin',
                Mage::app()->getRequest()->getParam('__amp_source_origin'),
                true
            )
            ->setBody(Mage::helper('core')->jsonEncode($data));
    }

    protected function _cropReviewData(array $reviewData)
    {
        $croppedValues = array();
        $allowedKeys = array_fill_keys(array('detail', 'title', 'nickname'), true);

        foreach ($reviewData as $key => $value) {
            if (isset($allowedKeys[$key])) {
                $croppedValues[$key] = $value;
            }
        }

        return $croppedValues;
    }
}
