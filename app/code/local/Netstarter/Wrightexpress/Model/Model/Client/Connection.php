<?php

class Netstarter_Wrightexpress_Model_Model_Client_Connection
{

    /**
     * Magento Configuration XML path to connection mode.
     */
    const CONFIG_PATH_MODE = 'wrightexpress/connection/mode';

    const CONFIG_PATH_MERCHANT_CODE = 'wrightexpress/connection/merchant_code';

    /**
     * Magento Configuration XML path to the production URL.
     */
    const CONFIG_PATH_URL_PRODUCTION = 'wrightexpress/connection/url_production';

    /**
     * Magento Configuration XML path to the production username.
     */
    const CONFIG_PATH_USERNAME_PRODUCTION = 'wrightexpress/connection/username_production';

    /**
     *  Magento Configuration XML path to the production password.
     */
    const CONFIG_PATH_PASSWORD_PRODUCTION = 'wrightexpress/connection/password_production';

    /**
     *  Magento Configuration XML path to the testing/sandbox URL.
     */
    const CONFIG_PATH_URL_SANDBOX = 'wrightexpress/connection/url_sandbox';

    /**
     *  Magento Configuration XML path to the testing/sandbox username.
     */
    const CONFIG_PATH_USERNAME_SANDBOX = 'wrightexpress/connection/username_sandbox';

    /**
     *  Magento Configuration XML path to the testing/sandbox password.
     */
    const CONFIG_PATH_PASSWORD_SANDBOX = 'wrightexpress/connection/password_sandbox';

    const XML_SERVICE_ROOT = '<SVML/>';

    const XML_ROOT_NODE = '<root />';


    private $_serviceClient = null;

    private $_params;

    private $_service;

    protected $_requestMessageId = null;


    protected function _getService()
    {
        return Mage::getStoreConfigFlag(self::CONFIG_PATH_MODE) ?
            Mage::getStoreConfig(self::CONFIG_PATH_URL_PRODUCTION) :
            Mage::getStoreConfig(self::CONFIG_PATH_URL_SANDBOX);
    }

    /**
     * API Username depending on Magento configuration.
     *
     * @return string
     */
    protected function _getUsername()
    {
        return Mage::getStoreConfigFlag(self::CONFIG_PATH_MODE) ?
            Mage::getStoreConfig(self::CONFIG_PATH_USERNAME_PRODUCTION) :
            Mage::getStoreConfig(self::CONFIG_PATH_USERNAME_SANDBOX);
    }

    /**
     * API Password depending on Magento configuration.
     *
     * @return string
     */
    protected function _getPassword()
    {
        return Mage::getStoreConfigFlag(self::CONFIG_PATH_MODE) ?
            Mage::getStoreConfig(self::CONFIG_PATH_PASSWORD_PRODUCTION) :
            Mage::getStoreConfig(self::CONFIG_PATH_PASSWORD_SANDBOX);
    }

    protected function _getMerchantCode()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_MERCHANT_CODE);
    }

    /**
     * Returns SoapClient singleton.
     *
     * @return null|SoapClient
     */
    public function getClient()
    {
        if(!$this->_serviceClient){

            try{

                $this->_serviceClient = new Varien_Http_Client($this->_getService());

                $this->_serviceClient->setMethod(Varien_Http_Client::POST);

            }catch(Exception $e){
                Mage::logException($e);
            }
        }

        return $this->_serviceClient;
    }

    private function getResponse()
    {
        $params = $this->_params;

        if (!is_object($params)){

            Mage::throwException(
                Mage::helper('wrightexpress')->__("Please set parameters before Soap call.")
            );
        }

        $result = $this->_serviceClient->setRawData($params->asXML(), 'text/xml')->request()->getBody();
        $response = new SimpleXMLElement($result);

        return $response;
    }

    private function prepareParams($params)
    {
        $this->_requestMessageId = uniqid();
        if (!is_array($params) && !is_object($params))
        {
            Mage::throwException(
                Mage::helper('wrightexpress')->__("Parameters need to be provided as an array or object.")
            );
        }


        // generate basic keyhole xml structure

        $requestXML = new SimpleXMLElement(self::XML_SERVICE_ROOT);
        $requestXML->addAttribute('xmlns', 'http://ecomindustries.com.au/SVML/');

        $serviceXML = $requestXML->addChild($this->_service);
        $serviceXML->addChild('RequestMessageId', $this->_requestMessageId);

        /**
         * auth
         */
        $auth = $serviceXML->addChild('SignonRequest');
        $auth->addChild('UserName', $this->_getUsername());
        $auth->addChild('Password', $this->_getPassword());


        if (is_object($params)){

            $toDom = dom_import_simplexml($serviceXML);

            foreach ($params->children() as $second_gen)
            {
                $fromDom = dom_import_simplexml($second_gen);
                $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
            }
        }

        $this->_params = $requestXML;
        return $this;
    }


    public function clearConnection()
    {
        $this->getClient();

        $this->_params = null;

        return $this;
    }

    public function setService($service)
    {
        $this->_service = $service;

        return $this;
    }


    public function getResult($service, $params, $rootNode = null)
    {
        try
        {
            $result = $this->clearConnection()
                ->setService($service)
                ->prepareParams($params)
                ->getResponse();

            if (property_exists($result, 'ErrorResponse')){

                Mage::throwException($result->ErrorResponse->errorMessage);
            }

            return $result;

        }catch(Mage_Core_Exception $e){

            Mage::logException($e);
        }catch(Exception $e){

            Mage::logException($e);
        }
        return false;
    }

}