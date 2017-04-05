<?php

/**
 * Class Netstarter_Retaildirections_Model_Client_Connection
 *
 * Handles all connection to RDWS API.
 * Usage is: "Netstarter_Retaildirections_Model_Client_Connection::getResult(Service, XML|Array Params)".
 * All dynamic parametrization should come from system.xml - or system backend.
 * If strings are used more than once in the code they were turned into constants.
 *
 * CHANGES IN THIS FILE SHOULD BE DISCUSSED AS IT AFFECTS ALL API CLIENTS WRITTEN SO FAR.
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 */
class Netstarter_Retaildirections_Model_Client_Connection
{
    /**
     * Magento Configuration XML path to connection mode.
     */
    const CONFIG_PATH_MODE = 'netstarter_retaildirections/connection/mode';

    /**
     * Magento Configuration XML path to the production URL.
     */
    const CONFIG_PATH_URL_PRODUCTION = 'netstarter_retaildirections/connection/url_production';

    /**
     * Magento Configuration XML path to the production username.
     */
    const CONFIG_PATH_USERNAME_PRODUCTION = 'netstarter_retaildirections/connection/username_production';

    /**
     *  Magento Configuration XML path to the production password.
     */
    const CONFIG_PATH_PASSWORD_PRODUCTION = 'netstarter_retaildirections/connection/password_production';

    /**
     *  Magento Configuration XML path to the testing/sandbox URL.
     */
    const CONFIG_PATH_URL_SANDBOX = 'netstarter_retaildirections/connection/url_sandbox';

    /**
     *  Magento Configuration XML path to the testing/sandbox username.
     */
    const CONFIG_PATH_USERNAME_SANDBOX = 'netstarter_retaildirections/connection/username_sandbox';

    /**
     *  Magento Configuration XML path to the testing/sandbox password.
     */
    const CONFIG_PATH_PASSWORD_SANDBOX = 'netstarter_retaildirections/connection/password_sandbox';

    /**
     *  SOAP Namespace used when building the SOAP Request.
     */
    const SOAP_NAMESPACE = 'http://www.retaildirections.com/';

    /**
     *  SOAP service used when building the SOAP Request.
     */
    const SOAP_SERVICE = 'RDService';

    /**
     * @var SoapClient singleton to be used for all API calls.
     */
    protected $_soapClient = null;

    /**
     * @var array Prepared parameters array. It is created before the API call with the XML/SOAP request.
     */
    protected $_params = null;
    
    /**
     * @var string To be used in case the root node of the xml request is different than the service name.
     */
    protected $_rootNode = null;

    /**
     * @var string Contains the service we are calling (API method inside the keyhole).
     */
    protected $_service = null;

    /**
     * @var string Version number of the API. So far no reason to use other value than 1.
     * If a method requires a different version than 1, setVersion should be called before
     * the getResult method.
     */
    protected $_version = '1';
    
    protected $_logModel = null;
    
    protected $_jobId           = 'CONNECTION';

    /**
     * API WSDL url depending on Magento configuration.
     *
     * @return string
     */
    protected function _getWsdl()
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

    /**
     * Returns SoapClient singleton.
     *
     * @return null|SoapClient
     */
    public function getClient()
    {
        if ($this->_soapClient == null)
        {
            try
            {
                ini_set("default_socket_timeout", 120);
                
                $this->_soapClient = @new SoapClient($this->_getWsdl(),
                    array(
                        'trace'=>true,
                        'connection_timeout'=>120,
                        'exceptions'=>1,
                    )
                );
            }
            catch (SoapFault $fault){

                $this->_log(array('SOAP Fault', $fault->faultstring));
            }
            catch (Exception $e)
            {
                $this->_log(array('Connection error', $e->getMessage()));
            }
        }

        return $this->_soapClient;
    }

    /**
     * Sets the name of the service for the next API call.
     *
     * @param $string
     * @return Netstarter_Retaildirections_Model_Client_Connection
     */
    public function setService($string)
    {
        $this->_service = $string;
        return $this;
    }
    
    /**
     * Gets the name of the service for the next API call.
     * @return null|string
     */
    public function getService()
    {
        return $this->_service;
    }
    
    /**
     * Sets the name of the root node for the next API call.
     *
     * @param $string
     * @return Netstarter_Retaildirections_Model_Client_Connection
     */
    public function setRootNode($string = null)
    {
        $this->_rootNode = $string;
        return $this;
    }
    
    /**
     * Gets the name of the root node for the next API call.
     * @return null|string
     */
    public function getRootNode()
    {
        return $this->_rootNode;
    }

    /**
     * Sets version of the API service being called.
     * Should be called before getResult() only if necessary.
     * Standard value is version 1.
     *
     * @param $version string
     * @return Netstarter_Retaildirections_Model_Client_Connection
     */
    public function setVersion($version)
    {
        $this->_version = $version;
        return $this;
    }

    /**
     * Returns current version for the service that will be called next.
     *
     * @return null|string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Actually does the Soap Call.
     *
     * @return SimpleXMLElement
     */
    public function getResponse()
    {
        $params = $this->getParams();

        if (!is_array($params) && !is_object($params))
        {
            Mage::throwException(
                Mage::helper('netstarter_retaildirections')->__("Please set parameters before Soap call.")
            );
        }

        $result = $this->getClient()->__soapCall(self::SOAP_SERVICE, $params);
        $response = new SimpleXMLElement($result->RDServiceResult);

        return $response;
    }

    /**
     * Returns the prepared parameters.
     *
     * @return null|array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Returns the prepared parameters.
     *
     * Array can be used as parameters for simple calls.
     * SimpleXMLElement can be used as parameters for complex calls.
     *
     * @param $params SimpleXMLElement|array
     * @return Netstarter_Retaildirections_Model_Client_Connection
     */
    public function prepareParams($params)
    {
        if (!is_array($params) && !is_object($params))
        {
            Mage::throwException(
                Mage::helper('netstarter_retaildirections')->__("Parameters need to be provided as an array or object.")
            );
        }
        
        /*
         * If this call requires a different root node
         * tries to use it, otherwise uses the service name.
         * 
         * This is due to several inconsistencies on the way parameters
         * are built into the request.
         */
        $rootNode = (is_string($this->getRootNode()) &&
                    $this->getRootNode() != null) ? $this->getRootNode() : $this->getService();
        
        
        // generate basic keyhole xml structure
        
        $requestXML = new SimpleXMLElement("<".$rootNode."Request />");
        $requestXML->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $requestXML->addAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
        $requestXML->addAttribute('xmlns', self::SOAP_NAMESPACE);

        if (is_array($params))
        {
            $serviceXML = $requestXML->addChild($rootNode);
            foreach($params as $key => $value)
            {
                $serviceXML->addChild($key, $value);
            }
        }
        else if (is_object($params))
        {
            $toDom = dom_import_simplexml($requestXML);

            foreach ($params->children() as $second_gen)
            {
                $fromDom = dom_import_simplexml($second_gen);
                $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
            }
        }
        
        Mage::log($requestXML->asXML(), null, "soap_requests.log");
        
        $this->_params = array(self::SOAP_SERVICE => array('request'=>$requestXML->asXML()));
        return $this;
    }

    /**
     * Prepares Soap Headers.
     *
     * @return Netstarter_Retaildirections_Model_Client_Connection
     */
    public function prepareHeaders()
    {
        $header = new SOAPHeader(
            self::SOAP_NAMESPACE,
            'SecurityToken',
            array(
                'Username' => $this->_getUsername(),
                'Password' => $this->_getPassword(),
                'ServiceName' => '',
                'ServiceVersion' => $this->getVersion(),
                'ServiceName'=> $this->getService()
            )
        );

        $this->getClient()->__setSoapHeaders($header);

        return $this;
    }

    /**
     * Clear current connection for a new API call.
     * It is automatically called on getResult()
     *
     * @return Netstarter_Retaildirections_Model_Client_Connection
     */
    public function clearConnection()
    {
        $this->getClient()->__setSoapHeaders(null);

        $this->_params = null;
        $this->_service = null;

        return $this;
    }

    /**
     * Prepares the call for the respective service, does the call, return results.
     *
     * @param $service string
     * @param $params SimpleXMLElement|array
     * @return SimpleXMLElement|bool
     */
    public function getResult($service, $params, $rootNode = null)
    {
        try
        {
            $result = $this->clearConnection()
                ->setRootNode($rootNode)
                ->setService($service)
                ->prepareHeaders()
                ->prepareParams($params)
                ->getResponse();
            
            if (property_exists($result, 'ErrorResponse'))
            {
                $this->_log(array('API RESPONSE ERROR', $service, $this->_paramsAsString($result->ErrorResponse->errorMessage)));
            }
            
            Mage::log($result, null, "soap_requests.log");

            return $result;
        }
        catch(Exception $e)
        {
            /*
             * @TODO turn into a log.
             */
            $this->_log(array('API REQUEST ERROR', $service, $this->_paramsAsString($params), $e->getMessage()));
        }
        return false;
    }

    /**
     * Special logging. For more detail please see Netstarter_Retaildirections_Model_Log.
     * Accepts an array, that is imploded. Adds memory usage information and information headers
     * to the log.
     *
     * @param array $message
     * @param null $level
     */
    protected function _log($message = array(), $level = null)
    {
        if ($this->_logModel == null)
        {
            $this->_logModel = Mage::getSingleton("netstarter_shelltools/log");
            $this->_logModel->setId($this->_jobId);
        }
        
        $this->_logModel->log($message, $level);
    }
    
    public function setLogModel($logModel)
    {
        $this->_logModel = $logModel;
    }
    
    protected function _paramsAsString($params)
    {
        if ($params instanceof SimpleXMLElement)
        {
            $paramsString = $params->asXML();
        }
        else if (is_array($params))
        {
            $paramsString = implode(', ', $params);
        }
        else
        {
            $paramsString = (string)$params;
        }
        return $paramsString;
    }
}