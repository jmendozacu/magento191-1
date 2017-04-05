<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 9/11/13
 * Time: 12:45 PM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_Wrightexpress_Model_Model_Giftcard extends Netstarter_Wrightexpress_Model_Model_Client_Connection
{


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
        $params = new SimpleXMLElement(self::XML_ROOT_NODE);
        $redemptionRequests = $params->addChild('BalanceCheckRequests');

        $redemptionRequest = $redemptionRequests->addChild('BalanceCheckRequest');
        $redemptionRequest->addChild('CredentialCode', $giftCardCode);
        $redemptionRequest->addChild('MerchantOutletCode', $this->_getMerchantCode());
        $redemptionRequest->addChild('AccessPassword', $pinCode);

        $resultXml = $this->getResult('BalanceCheckRequestMessage', $params);

        if($resultXml){

            if($result = $resultXml->BalanceCheckResponseMessage->BalanceCheckResponses->BalanceCheckResponse){
                $resultValid = $result->Result;

                $resultDesc = (string)$resultValid->Description;
                $resultCode = (int)$resultValid->ResultCode;

                if($resultCode === 0){

                    $currentBalance = (string)$result->CurrentBalance;

                    $data = array('balance' => $currentBalance, 'additional' => '');
                    return $data;
                }

                throw new Netstarter_GiftCardApi_Exception("Wright Express Giftcard: {$resultDesc} ({$giftCardCode})", $resultCode);
            }
        }
        Mage::throwException('Your request cannot be processed at this moment');
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
    public function redeemGiftCard($giftCardCode, $amount, $pinCode = null)
    {
        $params = new SimpleXMLElement(self::XML_ROOT_NODE);
        $redemptionRequests = $params->addChild('RedemptionRequests');

        $redemptionRequest = $redemptionRequests->addChild('RedemptionRequest');
        $redemptionRequest->addChild('CredentialCode', $giftCardCode);
        $redemptionRequest->addChild('MerchantOutletCode', $this->_getMerchantCode());
        $redemptionRequest->addChild('CredentialPassword', $pinCode);
        $redemptionRequest->addChild('Amount', $amount);

        $resultXml = $this->getResult('RedemptionRequestMessage' , $params);

        if($resultXml){

            if($result = $resultXml->RedemptionResponseMessage->RedemptionResponses->RedemptionResponse){
                $resultValid = $result->Result;

                $resultDesc = (string)$resultValid->Description;
                $resultCode = (int)$resultValid->ResultCode;

                if($resultCode === 0){

                    $transactionId = (string)$result->TransactionId;
                    return $transactionId;
                }

                throw new Netstarter_GiftCardApi_Exception("Wright Express Giftcard: {$resultDesc} ({$giftCardCode})", $resultCode);
            }
        }
        Mage::throwException('Your request cannot be processed at this moment');
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
        $params = new SimpleXMLElement(self::XML_ROOT_NODE);
        $redemptionRequests = $params->addChild('RedemptionRequests');

        foreach($cards as $card){

            $redemptionRequest = $redemptionRequests->addChild('RedemptionRequest');
            $redemptionRequest->addChild('CredentialCode', $card['c']);
            $redemptionRequest->addChild('MerchantOutletCode', $this->_getMerchantCode());
            $redemptionRequest->addChild('CredentialPassword', $card['p']);
            $redemptionRequest->addChild('Amount', $card['ba']);
        }

        $resultXml = $this->getResult('RedemptionRequestMessage' , $params);

        if(isset($params)){
            Mage::log($params, null, 'WE_GIFT.log');
        }

        if(isset($resultXml)){
            Mage::log($resultXml, null, 'WE_GIFT.log');
        }

        if($resultXml){

            $responsesXml = (array)$resultXml->RedemptionResponseMessage->RedemptionResponses;

            $transactionResponse = array();
            $errors = $credentialCode = '';
            $processedGCCount = 0;

            if(!empty($responsesXml['RedemptionResponse'])){

                $redemptionResponse = $responsesXml['RedemptionResponse'];

                if(is_array($redemptionResponse)){

                    foreach($redemptionResponse as $response){

                        if($resultValid = $response->Result){

                            $resultDesc = (string)$resultValid->Description;
                            $resultCode = (int)$resultValid->ResultCode;
                            $credentialCode = (string)$response->CredentialCode;

                            if($resultCode === 0){


                                $transactionId = (string)$response->TransactionId;
                                $transactionResponse[$this->_requestMessageId] = array('trans_id' => $transactionId);
                                ++$processedGCCount;
                            }else{

                                $errors .= "Wright Express Giftcard: {$resultDesc} ($credentialCode) \n";
                            }
                        }
                    }
                }elseif(is_object($redemptionResponse)){

                    if($resultValid = $redemptionResponse->Result){

                        $resultDesc = (string)$resultValid->Description;
                        $resultCode = (int)$resultValid->ResultCode;
                        $credentialCode = (string)$redemptionResponse->CredentialCode;

                        if($resultCode === 0){

                            $transactionId = (string)$redemptionResponse->TransactionId;
                            $transactionResponse[$this->_requestMessageId] = array('trans_id' => $transactionId);
                            ++$processedGCCount;
                        }else{

                            $errors .= "Wright Express Giftcard: {$resultDesc} ($credentialCode) \n";
                        }
                    }
                }
            }

            if(!empty($transactionResponse) &&  count($cards) == $processedGCCount){

                return $transactionResponse;
            }else{

                $this->cancelGiftCardRedeem($this->_requestMessageId);
                throw new Netstarter_GiftCardApi_Exception(($errors?$errors:"Wright Express Giftcard Error"), 0003);
            }

        }
        Mage::throwException('Your request cannot be processed at this moment');
    }

    /**
     * Redeem Gift cards
     *
     * @param $redeemRequestMessageId
     * @return bool
     */
    public function cancelGiftCardRedeem($redeemRequestMessageId)
    {
        $params = new SimpleXMLElement(self::XML_ROOT_NODE);
        $redemptionRequest = $params->addChild('UndoRequest');

        $redemptionRequest->addChild('OriginalRequestMessageId', $redeemRequestMessageId);

        $resultXml = $this->getResult('UndoRequestMessage' , $params);

        if(isset($params)){
            Mage::log($params, null, 'WE_GIFT.log');
        }

        if(isset($resultXml)){
            Mage::log($resultXml, null, 'WE_GIFT.log');
        }

        if($resultXml){

            if($result = $resultXml->UndoResponseMessage->UndoResponse){
                $resultValid = $result->Result;

                $resultCode = (int)$resultValid->ResultCode;

                if($resultCode === 0){

                    return true;
                }
            }
        }

        return false;
    }
}