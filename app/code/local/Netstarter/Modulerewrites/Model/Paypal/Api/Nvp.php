<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 11/15/13
 * Time: 3:51 PM
 * Hack to avoid Paypal order rejection error
 *
 * exception 'Mage_Core_Exception' with message 'PayPal NVP gateway errors:
 * The totals of the cart item amounts do not match order amounts (#10413: Transaction refused because of an invalid argument.
 * See additional error messages for details). Correlation ID: 14450b2be0a4f. Version: 72.0.'
 * in /home/brasnthings/public_html/app/Mage.php:579
 *
 */ 
class Netstarter_Modulerewrites_Model_Paypal_Api_Nvp extends Mage_Paypal_Model_Api_Nvp
{

    /**
     * SetExpressCheckout call
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_SetExpressCheckout
     *
     */
    public function callSetExpressCheckout()
    {
        $this->_prepareExpressCheckoutCallRequest($this->_setExpressCheckoutRequest);
        $request = $this->_exportToRequest($this->_setExpressCheckoutRequest);
        $this->_exportLineItems($request);

        // import/suppress shipping address, if any
        $options = $this->getShippingOptions();
        if ($this->getAddress()) {
            $request = $this->_importAddresses($request);
            $request['ADDROVERRIDE'] = 1;
        } elseif ($options && (count($options) <= 10)) { // doesn't support more than 10 shipping options
            $request['CALLBACK'] = $this->getShippingOptionsCallbackUrl();
            $request['CALLBACKTIMEOUT'] = 6; // max value
            $request['MAXAMT'] = $request['AMT'] + 999.00; // it is impossible to calculate max amount
            $this->_exportShippingOptions($request);
        }

        // add recurring profiles information
        $i = 0;
        foreach ($this->_recurringPaymentProfiles as $profile) {
            $request["L_BILLINGTYPE{$i}"] = 'RecurringPayments';
            $request["L_BILLINGAGREEMENTDESCRIPTION{$i}"] = $profile->getScheduleDescription();
            $i++;
        }

        //Hack Start

        if(isset($request['TAXAMT']) && isset($request['ITEMAMT'])){

            $request['TAXAMT'] = round($request['TAXAMT'], 2);
            $request['ITEMAMT'] = round($request['ITEMAMT'], 2);
            $request['AMT'] = round($request['AMT'], 2);

            $totalValue = $request['TAXAMT'] + $request['ITEMAMT'];
            $finalValue = $totalValue - $request['AMT'];

            if($request['SHIPPINGAMT'] > 0) {

                $request['SHIPPINGAMT'] = ($request['AMT'] - ($request['TAXAMT'] + $request['ITEMAMT']));
                $totalValue = $request['TAXAMT'] + $request['ITEMAMT'] + $request['SHIPPINGAMT'];
                $finalValue = $totalValue - $request['AMT'];

            }

            if($request['AMT'] != $totalValue) {

                if($totalValue > $request['AMT']) {
                    $request['TAXAMT'] = $request['TAXAMT'] - $finalValue;
                }elseif($totalValue < $request['AMT']) {
                    $request['TAXAMT'] = $request['TAXAMT'] + $finalValue;
                }else{
                    $request['AMT'] = $request['TAXAMT'] + $request['ITEMAMT'];
                }
            }
        }


        //Hack End

        // Check discount
        $lineAmt = 0;
        $discount = 0;
        $line = 0;
        $discountIndex = 0;
        while(isset($request["L_AMT{$line}"])) {
            if($request["L_NAME{$line}"] == 'Discount') {
                $discount += $request["L_AMT{$line}"];
                $discountIndex = $line;
            }
            else {
                $lineAmt += $request["L_AMT{$line}"];
            }
            $line++;
        }

        if($discount) {
            if($discount + $lineAmt != $request['ITEMAMT']) {
// Do correction
                $request["L_AMT{$discountIndex}"] = ($request['ITEMAMT'] - $lineAmt);
            }
        } else {
// Check item amount adds up
            $correctItemAmt = $request['AMT'] - $request['SHIPPINGAMT'] - $request['TAXAMT'];
            if ($correctItemAmt != $request['ITEMAMT']) {
                $request['ITEMAMT'] = $correctItemAmt;
            }
        }
            $response = $this->call(self::SET_EXPRESS_CHECKOUT, $request);
            $this->_importFromResponse($this->_setExpressCheckoutResponse, $response);

    }


    /**
     * Do the API call
     *
     * @param string $methodName
     * @param array $request
     * @return array
     * @throws Mage_Core_Exception
     */
    public function call($methodName, array $request)
    {
        $request = $this->_addMethodToRequest($methodName, $request);
        $eachCallRequest = $this->_prepareEachCallRequest($methodName);
        if ($this->getUseCertAuthentication()) {
            if ($key = array_search('SIGNATURE', $eachCallRequest)) {
                unset($eachCallRequest[$key]);
            }
        }

        //Hack Start

        if(isset($request['TAXAMT']) && isset($request['ITEMAMT'])){

            $request['TAXAMT'] = round($request['TAXAMT'], 2);
            $request['ITEMAMT'] = round($request['ITEMAMT'], 2);
            $request['SHIPPINGAMT'] = round($request['SHIPPINGAMT'], 2);
            $request['AMT'] = round($request['AMT'], 2);

            $totalValue = $request['TAXAMT'] + $request['ITEMAMT'] + $request['SHIPPINGAMT'];
            $finalValue = $totalValue - $request['AMT'];

            if($request['AMT'] != $totalValue) {

                if($totalValue > $request['AMT']) {
                    if($finalValue > 0) {

                        $request['TAXAMT'] = $request['TAXAMT'] - $finalValue;
                    }else{
                        $request['AMT'] = $totalValue;
                    }
                } elseif($totalValue < $request['AMT']) {

                    if($finalValue > 0) {
                        // its preferable that we change the tax amount over the grand total amount
                        $request['TAXAMT'] = $request['TAXAMT'] + $finalValue;
                    }else{
                        $request['AMT'] = $totalValue;
                    }
                }else{
                    $request['AMT'] = $totalValue;
                }
            }
        }

        ///Hack End


        $request = $this->_exportToRequest($eachCallRequest, $request);
        $debugData = array('url' => $this->getApiEndpoint(), $methodName => $request);

        try {
            $http = new Varien_Http_Adapter_Curl();
            $config = array(
                'timeout'    => 60,
                'verifypeer' => $this->_config->verifyPeer
            );

            if ($this->getUseProxy()) {
                $config['proxy'] = $this->getProxyHost(). ':' . $this->getProxyPort();
            }
            if ($this->getUseCertAuthentication()) {
                $config['ssl_cert'] = $this->getApiCertificate();
            }
            $http->setConfig($config);
            $http->write(
                Zend_Http_Client::POST,
                $this->getApiEndpoint(),
                '1.1',
                $this->_headers,
                $this->_buildQuery($request)
            );
            $response = $http->read();
        } catch (Exception $e) {
            $debugData['http_error'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $this->_debug($debugData);
            throw $e;
        }

        $response = preg_split('/^\r?$/m', $response, 2);
        $response = trim($response[1]);
        $response = $this->_deformatNVP($response);

        $debugData['response'] = $response;
        $this->_debug($debugData);
        $response = $this->_postProcessResponse($response);

        // handle transport error
        if ($http->getErrno()) {
            Mage::logException(new Exception(
                sprintf('PayPal NVP CURL connection error #%s: %s', $http->getErrno(), $http->getError())
            ));
            $http->close();

            Mage::throwException(Mage::helper('paypal')->__('Unable to communicate with the PayPal gateway.'));
        }

        // cUrl resource must be closed after checking it for errors
        $http->close();

        if (!$this->_validateResponse($methodName, $response)) {
            Mage::logException(new Exception(
                Mage::helper('paypal')->__("PayPal response hasn't required fields.")
            ));
            Mage::throwException(Mage::helper('paypal')->__('There was an error processing your order. Please contact us or try again later.'));
        }

        $this->_callErrors = array();
        if ($this->_isCallSuccessful($response)) {
            if ($this->_rawResponseNeeded) {
                $this->setRawSuccessResponseData($response);
            }
            return $response;
        }
        $this->_handleCallErrors($response);
        return $response;
    }

}
