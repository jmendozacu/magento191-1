<?php

/*
 * -> To get the store id: $this->getStoreId()
 * -> To get standard supply channel id: $this->getSupplyChannelId()
 * -> If new global constants are needed to be added to the code, it can
 * be added to Magento Backend and a getter can be added to the parent class Netstarter_Retaildirections_Model_Abstract.
 */

/**
 * Class Netstarter_Retaildirections_Model_Model_Product
 *
 * Class that imports products from the API to the Database.
 * Uses Netstarter_Retaildirections_Model_Client_Connection to handle soap connection.
 *
 */
class Netstarter_Retaildirections_Model_Model_Giftcard extends Netstarter_Retaildirections_Model_Model_Abstract
{

    /**
     * Service on the API to get order details.
     */
    const API_METHOD_GC_REQUEST_SERVICE  = 'DoGiftVoucherRequest';

    const API_METHOD_GC_TRANS_SERVICE  = 'DoVoucherTransaction';

    const API_METHOD_GC_ENQUIRY_SERVICE  = 'GetVoucherEnquire';

    const API_METHOD_GC_TRANS_ACTION = 'VoucherTransaction';

    const API_METHOD_GC_REQUEST_ACTION = 'VoucherRequest';

    const API_METHOD_GC_Enquiry_ACTION = 'VoucherEnquiry';

    const API_METHOD_REF_TYPE_IND = 'G';

    const GC_CONFIG_PATH = 'netstarter_retaildirections/gcsetting';


    protected $_jobId           = 'GIFTCARD';
    protected $_logReportMode   = self::LOG_REPORT_MODE_LOG;
    protected $_logXmlPath      = 'netstarter_retaildirections/giftcard/log_file';


    public function getGcConfigs($path = null)
    {
        return Mage::getStoreConfig(self::GC_CONFIG_PATH.$path);
    }

    protected function _doVoucherTransaction($elements)
    {
        $params = new SimpleXMLElement(self::XML_ROOT_NODE);
        $voucher = $params->addChild(self::API_METHOD_GC_TRANS_ACTION);

        foreach($elements as $node=>$element){

            $voucher->addChild($node, $element);
        }

        // Performs the actual call to the API.
        $result =  $this->getConnectionModel()->getResult(self::API_METHOD_GC_TRANS_SERVICE, $params, self::API_METHOD_GC_TRANS_ACTION);

        if (!empty($result->VoucherTransaction)){

            return $result->VoucherTransaction;

        }elseif(!empty($result->ErrorResponse)){

            throw new Netstarter_GiftCardApi_Exception("RD Giftcard: {$result->ErrorResponse->errorMessage}", (int)$result->ErrorResponse->errorNumber);

        }

        return false;
    }

    protected function _createGiftCard($elements)
    {
        $params = new SimpleXMLElement(self::XML_ROOT_NODE);
        $voucher = $params->addChild(self::API_METHOD_GC_REQUEST_ACTION);

        foreach($elements as $node=>$element){

            $voucher->addChild($node, $element);
        }

        // Performs the actual call to the API.
        $result =  $this->getConnectionModel()->getResult(self::API_METHOD_GC_REQUEST_SERVICE, $params, self::API_METHOD_GC_REQUEST_ACTION);

        if (!empty($result->VoucherRequest)){

            return $result->VoucherRequest;

        }elseif(!empty($result->ErrorResponse)){

            throw new Netstarter_GiftCardApi_Exception("RD Giftcard: {$result->ErrorResponse->errorMessage}", (int)$result->ErrorResponse->errorNumber);

        }

        return false;
    }

    protected function _doEnquiry($elements)
    {
        $params = new SimpleXMLElement(self::XML_ROOT_NODE);
        $voucher = $params->addChild('VoucherDetails');

        foreach($elements as $node=>$element){

            $voucher->addChild($node, $element);
        }

        // Performs the actual call to the API.
        $result =  $this->getConnectionModel()->getResult(self::API_METHOD_GC_ENQUIRY_SERVICE, $params, self::API_METHOD_GC_Enquiry_ACTION);

        if (!empty($result->VoucherDetails)){

            return $result->VoucherDetails;
        }elseif(!empty($result->ErrorResponse)){

            throw new Netstarter_GiftCardApi_Exception("RD Giftcard: {$result->ErrorResponse->errorMessage}", (int)$result->ErrorResponse->errorNumber);

        }

        return false;
    }


    /**
     * Generate a Unique id for doc_line_id
     *
     * @return string
     */
    private function getUniqueId()
    {
        $unique = uniqid();
        $lineId = substr($unique, -12);

        return $lineId;
    }

    public function issueGiftCard($card, $code)
    {
        try{
            if($card && $card->getOrderItem()){

                $order = $card->getOrderItem()->getOrder();

                $elements = array();
                $elements['reference_type_ind']  = self::API_METHOD_REF_TYPE_IND;
                $elements['doc_line_id']  = $this->getUniqueId();
                $elements['clienttype_ind']  = 'R';
                $elements['giftvoucherscheme_code'] = $this->getGcConfigs('/scheme_code');
                $elements['giftvoucher_reference'] = $code->getCode();
                $elements['location_code'] = $this->getStoreId();
                $elements['tran_type'] = 'I';
                $elements['tran_datetime'] = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
                $elements['tran_currency'] = $order->getOrderCurrencyCode();
                $elements['tran_amount'] = $card->getAmount();

                $customer = $order->getCustomer();
                if($customer){

                    $elements['user_code'] = $customer->getRdId();
                }

                $this->_doVoucherTransaction($elements);
            }

        }catch (SoapFault $fault){

            $this->_log(array('SOAP Fault', $fault->faultstring));
        }catch (Exception $e){

            $this->_log($e->getMessage(), Zend_Log::ERR);
        }
    }


    public function createGiftCard($card, $code)
    {
        try
        {
            if($card && $item = $card->getOrderItem()){

                $order = $card->getOrderItem()->getOrder();

                $elements = array();
                $elements['giftvoucherscheme_code']  = $this->getGcConfigs('/scheme_code');
                $elements['store_code']  = $this->getStoreId();
                $elements['issued_currency_code'] = $order->getOrderCurrencyCode();
                $elements['amount'] = $card->getAmount();
                $elements['reference_code'] = $order->getIncrementId();
                $elements['request_datetime'] =  date(DATE_ATOM, Mage::getModel('core/date')->timestamp(time()));

                $senderName = $item->getProductOptionByCode('giftcard_sender_name');
                $senderEmail = $item->getProductOptionByCode('giftcard_sender_email');
                $receiverName = $item->getProductOptionByCode('giftcard_recipient_name');
                $receiverEmail = $item->getProductOptionByCode('giftcard_recipient_email');
                $giftCardType = $item->getProductOptionByCode('giftcard_type');

                $elements['purchaser_first_name'] = htmlspecialchars($senderName);
                $elements['purchaser_email_address'] = $senderEmail;
                $elements['recipient_first_name'] = htmlspecialchars($receiverName);

                $shippingAddress = $order->getShippingAddress();

                if($shippingAddress){

                    $street = $shippingAddress->getStreet();
                    $elements['delivery_addr_1'] = htmlspecialchars((!empty($street[0])?$street[0]:''));
                    $elements['delivery_addr_2'] = htmlspecialchars((!empty($street[1])?$street[1]:''));
                    $elements['delivery_postcode'] = $shippingAddress->getPostcode();
                    $elements['delivery_city'] = htmlspecialchars($shippingAddress->getCity());
                    $elements['delivery_state_code'] = htmlspecialchars($shippingAddress->getRegionId());
                    $elements['delivery_country_code'] = $shippingAddress->getCountryId();
                }

                $elements['message'] =  htmlspecialchars($item->getProductOptionByCode('giftcard_message'));
                $elements['recipient_email_address'] = $receiverEmail;
                $elements['fulfilment_method_ind'] = ($giftCardType == Enterprise_GiftCard_Model_Giftcard::TYPE_VIRTUAL?'V':'P');

                $returnXml = $this->_createGiftCard($elements);

                if($returnXml){

                    if($refCode = (string)$returnXml->giftvoucher_reference){
                        $code->setCode($refCode);
                        $code->setPin((string)$returnXml->pin);
                    }elseif($refCode = (string)$returnXml->reference_code){
                        $code->setCode($refCode);
                        $code->setRedeem(1);
                    }
                }
            }

            return $this;

        }catch (SoapFault $fault){

            $this->_log(array('SOAP Fault', $fault->faultstring));
        }catch (Exception $e){

            $this->_log($e->getMessage(), Zend_Log::ERR);
        }
    }


    /**
     * Get gift card balance
     *
     * @param $giftCardCode
     * @param null $pinCode
     * @return string
     * @throws Netstarter_GiftCardApi_Exception
     */
    public function getBalance($giftCardCode, $pinCode = null)
    {
        try{
            if($giftCardCode){

                $elements = array();
                $elements['giftvoucher_reference']  = $giftCardCode;
//                $elements['giftvoucherscheme_code'] = $this->getGcConfigs('/scheme_code');
                $elements['location_code'] = $this->getStoreId();
                $elements['pin'] = $pinCode;

                $result = $this->_doEnquiry($elements);

                if($result){

                    $resultCode = $result->giftvoucher_reference;

                    if($resultCode){

                        $currentBalance = (string)$result->current_balance;
                        $schema = (string)$result->giftvoucherscheme_code;

                        $data = array('balance' => $currentBalance, 'additional' => $schema);

                        return $data;
                    }

                    throw new Netstarter_GiftCardApi_Exception("Retail Direction Giftcard: ({$giftCardCode})", $resultCode);
                }
            }

            Mage::throwException('Your request cannot be processed at this moment');

        }catch (Netstarter_GiftCardApi_Exception $e){

            $error = $e->getCode() == 56122 ? "RD Giftcard: Invalid giftvoucher reference or pin.": $e->getMessage();
            Mage::throwException($error);

        }catch (SoapFault $fault){

            $this->_log(array('SOAP Fault', $fault->faultstring));
            Mage::throwException('Your request cannot be processed at this moment');
        }catch (Exception $e){

            $this->_log($e->getMessage(), Zend_Log::ERR);
            Mage::throwException('Your request cannot be processed at this moment');
        }
    }

    /**
     * Redeem individual giftcard
     *
     * @param $giftCardCode
     * @param $amount
     * @param null $pinCode
     * @return string
     * @throws Netstarter_GiftCardApi_Exception
     */
    public function redeemGiftCard($card, $currency)
    {
        try{
            if($card){

                $elements = array();
                $elements['reference_type_ind']  = 'G';
                $elements['doc_line_id']  = $this->getUniqueId(); // shouldn't this be returned by the API?
                $elements['clienttype_ind']  = 'E';
//                $elements['giftvoucherscheme_code'] = $this->getGcConfigs('/scheme_code');
                $elements['giftvoucherscheme_code'] = $card['e'];
                $elements['giftvoucher_reference'] = $card['c'];
                $elements['location_code'] = $this->getStoreId();
                $elements['tran_type'] = 'R';
                $elements['tran_datetime'] =  date(DATE_ATOM, Mage::getModel('core/date')->timestamp(time()));
                $elements['tran_currency'] = $currency;
                $elements['tran_amount'] = $card['ba'];
                $elements['user_code'] = '1';
                $elements['pin'] = $card['p'];

                $result = $this->_doVoucherTransaction($elements);

                if(isset($elements)){
                    Mage::log($elements, null, 'RD_GIFT.log');
                }

                if(isset($result)){
                    Mage::log($result, null, 'RD_GIFT.log');
                }

                if($result && $transactionId = (string) $result->doc_line_id){

                    return $transactionId;
                }
            }

            Mage::throwException('Your request cannot be processed at this moment');

        }catch (Netstarter_GiftCardApi_Exception $e){

            Mage::throwException($e->getMessage());

        }catch (SoapFault $fault){

            $this->_log(array('SOAP Fault', $fault->faultstring));
            Mage::throwException('Your request cannot be processed at this moment');
        }catch (Exception $e){

            $this->_log($e->getMessage(), Zend_Log::ERR);
            Mage::throwException('Your request cannot be processed at this moment');
        }
    }

    /**
     * Redeem multiple gift cards at once
     *
     * @param $cards
     * @return array
     * @throws Netstarter_GiftCardApi_Exception
     */
    public function multipleRedeemGiftCard($cards)
    {
        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();

        $transactionResponse = array();
        $processedGCCount = 0;

        foreach($cards as $card){

            $transactionId = $this->redeemGiftCard($card, $currency);

            $transactionResponse[$transactionId] = array('trans_id' => $transactionId, 'card'=> $card, 'currency' => $currency);
            ++$processedGCCount;
        }


        if(!empty($transactionResponse) &&  count($cards) == $processedGCCount){

            return $transactionResponse;
        }else{

//            $this->cancelGiftCardRedeem($this->_requestMessageId);
            throw new Netstarter_GiftCardApi_Exception("Retail Direction Giftcard Error", 0003);
        }
    }

    /**
     * Redeem Gift cards
     *
     * @param $redeemRequestMessageId
     * @return bool
     */
    public function cancelGiftCardRedeem($transaction)
    {
        try{
            if($transaction){

                $elements = array();
                $elements['reference_type_ind']  = 'G';
                $elements['doc_line_id']  = $this->getUniqueId();
                $elements['clienttype_ind']  = 'E';
                $elements['giftvoucherscheme_code'] = $transaction['card']['e'];
                $elements['giftvoucher_reference'] = $transaction['card']['c'];
                $elements['location_code'] = $this->getStoreId();
                $elements['tran_type'] = 'V';
                $elements['tran_amount'] = $transaction['card']['a'];
                $elements['tran_datetime'] =  date(DATE_ATOM, Mage::getModel('core/date')->timestamp(time()));
                $elements['tran_currency'] = $transaction['currency'];
                $elements['user_code'] = '1';
                $elements['pin'] = $transaction['card']['p'];

                $result = $this->_doVoucherTransaction($elements);

                if(isset($elements)){
                    Mage::log($elements, null, 'RD_GIFT.log');
                }

                if(isset($result)){
                    Mage::log($result, null, 'RD_GIFT.log');
                }

                if (!empty($result->doc_line_id)){

                    return true;
                }
            }

            return false;

        }catch (Netstarter_GiftCardApi_Exception $e){

            $this->_log("RD Giftcard: {$e->getMessage()}",  Zend_Log::ERR);
            return false;

        }catch (SoapFault $fault){

            $this->_log(array('SOAP Fault', $fault->faultstring));
            Mage::throwException('Your request cannot be processed at this moment');
        }catch (Exception $e){

            $this->_log($e->getMessage(), Zend_Log::ERR);
            Mage::throwException('Your request cannot be processed at this moment');
        }
    }
}