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
class Netstarter_Retaildirections_Model_Model_Customer extends Netstarter_Retaildirections_Model_Model_Abstract
{

    /**
     * Service on the API to get customer details.
     */
    const API_METHOD_CUSTOMER_EDIT = 'CustomerEdit';

    const API_METHOD_CUSTOMER_SITE_EDIT = 'CustomerSiteEdit';

    const API_METHOD_CUSTOMER_GET_BY_ID = 'CustomerGet';

    const API_METHOD_CUSTOMER_GET_BY_EMAIL= 'CustomerGetByEmail';

    const API_METHOD_CUSTOMER_RESET_PASSWORD= 'ResetPassword';
    
    
    protected $_jobId           = 'CUSTOMER';
    protected $_logReportMode   = self::LOG_REPORT_MODE_LOG;
    protected $_logXmlPath      = 'netstarter_retaildirections/customer/log_file';
    

    private $_website = 0;


    protected $_rdCharLimits  = array(
        'Address1'=>  32,
        'Address2' => 32,
        'suburb'=>  32,
        'state'=> 12,
        'countryCode'=> 12,
        'postCode'=> 12,
        'firstName'=> 20,
        'surname'=> 20,
        'lastName'=> 30,
        'phone'=> 12,
        'emailAddress'=> 64
    );

    /**
     * Get customer details by Id
     *
     * @param $id
     *
     * @return null
     */
    public function customerGetById($id)
    {
        if(!empty($id)){

            $params = new SimpleXMLElement(self::XML_ROOT_NODE);
            $customer = $params->addChild('CustomerGet');

            $customer->addChild('customerId', $id);
            $customer->addChild('requestDateTime', date('Y-m-d'));

            $result =  $this->getConnectionModel()->getResult(self::API_METHOD_CUSTOMER_GET_BY_ID, $params);

            if (!empty($result->Customer)){

                return $result->Customer;
            }elseif(!empty($result->ErrorResponse)){

                throw new Exception("Customer Get: {$result->ErrorResponse->errorMessage}", (int)$result->ErrorResponse->errorNumber);
            }

        }

        return null;
    }


    /**
     * Get customer details by Email
     *
     * @param $email
     *
     * @return null
     */
    public function customerGetByEmail($email)
    {
        if(!empty($email)){

            $params = new SimpleXMLElement(self::XML_ROOT_NODE);
            $customer = $params->addChild('CustomerGetByEmail');

            $customer->addChild('emailAddress', $email);
            $customer->addChild('requestDateTime', date('Y-m-d'));

            $result =  $this->getConnectionModel()->getResult(self::API_METHOD_CUSTOMER_GET_BY_EMAIL, $params);


            if (!empty($result->Customers)){

                $refId = null;

                if(count($result->Customers) > 1){

                    foreach($result->Customers as $customerObj){

                        $customer = $customerObj->Customer;

                        if((string)$customer->customerStatusInd == 'A')
                            $refId = (string)$customer->customerId;
                    }

                }elseif(count($result->Customers) == 1){
                        $refId = (string)$result->Customers->Customer->customerId;
                }

                return $refId;

            }elseif(!empty($result->ErrorResponse)){
                if ((int)$result->ErrorResponse->errorNumber!=60103)
                throw new Exception("Customer Get: {$result->ErrorResponse->errorMessage}", (int)$result->ErrorResponse->errorNumber);
            }
        }

        return null;
    }


    /**
     * Edit and add new customers
     *
     * @param $elements
     *
     * @return bool|SimpleXMLElement[]
     */
    protected function _customerEdit($elements)
    {
        $params = new SimpleXMLElement(self::XML_ROOT_NODE);
        $customer = $params->addChild('Customer');


        $customer->addChild('homeLocationCode', $this->getStoreId($this->_website));
        $customer->addChild('origin', Mage::getBaseUrl());

        foreach($elements as $node=>$element){

            $customer->addChild($node, $element);
        }


        // Performs the actual call to the API.
        $result =  $this->getConnectionModel()->getResult(self::API_METHOD_CUSTOMER_EDIT, $params);


        if (!empty($result->Customer)){

            return $result->Customer;
        }elseif(!empty($result->ErrorResponse)){

            throw new Exception("Customer Save: {$result->ErrorResponse->errorMessage}", (int)$result->ErrorResponse->errorNumber);
        }

        return false;
    }


    /**
     * create locationRef for a particular customer
     *
     * get customer site
     *
     * @param $elements
     *
     */
    protected function _customerSiteEdit($elements)
    {

        $params = new SimpleXMLElement(self::XML_ROOT_NODE);
        $customer = $params->addChild('CustomerSite');

        foreach($elements as $node=>$element){

            $customer->addChild($node, $element);
        }

        // Performs the actual call to the API.
        $result =  $this->getConnectionModel()->getResult(self::API_METHOD_CUSTOMER_SITE_EDIT, $params);

        if (!empty($result->CustomerSite)){

            return $result->CustomerSite;
        }elseif(!empty($result->ErrorResponse)){

            throw new Exception("Customer Address Save: {$result->ErrorResponse->errorMessage}", (int)$result->ErrorResponse->errorNumber);
        }

        return false;
    }

    /**
     * reset password private method
     *
     * @param $elements
     *
     */
    private function _resetPassword($elements)
    {

        $params = new SimpleXMLElement(self::XML_ROOT_NODE);
        $customer = $params->addChild('ResetPassword');

        $customer->addChild('resetPasswordMethod', 'Direct');

        foreach($elements as $node=>$element){

            $customer->addChild($node, $element);
        }

        // Performs the actual call to the API.
        $result =  $this->getConnectionModel()->getResult(self::API_METHOD_CUSTOMER_RESET_PASSWORD, $params);

        if (isset($result->ResetPassword)){

            return $result->ResetPassword;
        }else
        {
            return false;
        }

    }

    /**
     * create customer public method
     *
     * internal validations need to do
     *
     * @param $params
     *
     * @return bool|SimpleXMLElement[]
     */
    public function createCustomer($customer)
    {
        try{

            $customerData = array('firstName'    => htmlspecialchars($customer->getFirstname()),
                                  'lastName'     => htmlspecialchars($customer->getLastname()),
                                  'emailAddress' => $customer->getEmail());

            $customerData = $this->formatRdFields($customerData);

            $this->_website = (int) $customer->getWebsiteId();

            $customerRdId = $customer->getRdId();

            if(!empty($customerRdId)){

                $customerData['customerId'] = $customerRdId;
            }else{

                $customerRead = $this->customerGetByEmail($customer->getEmail());

                if($customerRead){

                    $customerData['customerId'] = $customerRead;
                }
            }

            if($customer->getIsGuest())
                $customerData['customerStatusInd'] = 'G';

            $result = $this->_customerEdit($customerData);

            if (!empty($result)) {

                if(empty($customerRdId) && $customer->getId() && !$customer->getIsGuest())
                    $this->customAttributeUpdate('customer',$customer->getId(),'rd_id',$result->customerId);

                return (string)$result->customerId;
            }

            return null;

        }catch (SoapFault $fault){

            $this->_log(array('SOAP Fault', $fault->faultstring));
        }catch (Exception $e){

            if(isset($customerData)){
                Mage::log($e->getMessage(), null, 'RD_Customer_Err.log');
                Mage::log($customerData, null, 'RD_Customer_Err.log');
            }

            $this->_log($e->getMessage(), Zend_Log::ERR);
        }
    }

    /**
     * create customer sites public method
     *
     * internal validations need to do
     *
     * @param $params
     *
     * @return bool|null|SimpleXMLElement[]
     */
    public function createCustomerSite($customer, $address)
    {
        try{

            if($address instanceof Mage_Customer_Model_Address){

                $addressId = $address->getId();
                $addressHref = 'c_'.$addressId;

            }elseif($address instanceof Mage_Sales_Model_Order_Address){

                $addressId = $address->getCustomerAddressId();
                $addressHref = ($addressId)?'c_'.$addressId:'o_'.$address->getId();
            }else{
                return null;
            }

            $street = $address->getStreet();
            $compayName=$address->getCompany();
            $customerId = $customer->getRdId();
            If (!empty($compayName))
            {
                $address1=$compayName.' '.$street[0];
            }
            else
            {
                $address1=$street[0];
            }
            $addressData = array('locationRef'=> $addressHref,
                                 'customerId' => $customerId,
                                 'Address1'=>  $address1,
                                 'Address2'=>  (!empty($street[1])?' '.$street[1]:''),
                                 'suburb'=>  $address->getCity(),
                                 'state'=> $address->getRegion(),
                                 'countryCode'=> $address->getCountryId(),
                                 'postCode'=> $address->getPostcode(),
                                 'firstName'=> $address->getFirstname(),
                                 'surname'=> $address->getLastname(),
                                 'phone'=> $address->getTelephone()
            );

            //Format the data sending to RD for character limitations etc.

            $addressData = $this->formatRdFields($addressData);

            if($address->getDefaultAddress())
                $addressData['defaultInd'] = 'Y';


            if(!empty($addressData['locationRef']) && !empty($addressData['customerId'])){

                $result = $this->_customerSiteEdit($addressData);

                if(!empty($result)){

                    if(!$customer->getIsGuest() && $addressId){

                        $this->customAttributeUpdate('customer_address',$addressId,'location_ref',$addressHref);
                    }

                    return $addressHref;
                }
            }

            return null;

        }catch (SoapFault $fault){

            $this->_log(array('SOAP Fault', $fault->faultstring));
        }catch (Exception $e){

            if(isset($addressData)){

                Mage::log($e->getMessage(), null, 'RD_Customer_Err.log');
                Mage::log($addressData, null, 'RD_Customer_Err.log');
            }

            $this->_log($e->getMessage(), Zend_Log::ERR);
        }
    }

    /**
     * Format the data sending to RD for character limitations etc.
     * @param array $addressData
     * @return array
     */
    public function formatRdFields($addressData = array())
    {
        try {

            if ($addressData) {
                foreach ($addressData as $field => $value) {
                    if ($field == 'state') {
                        if (isset($addressData['countryCode']) && $addressData['countryCode'] == 'AU') {

                            $collection = Mage::getResourceModel('location/main');
                            $stateCode = $collection->getStateShortCode($addressData['countryCode'], $value);
                            if ($stateCode) {
                                $value = $stateCode;
                            }
                        }
                    }

                    $charLimit = isset($this->_rdCharLimits[$field]) ? $this->_rdCharLimits[$field] : false;

                    if ($charLimit !== false) {
                        $value = substr($value, 0, intval($charLimit));
                    }
                    $addressData[$field] = htmlspecialchars($value);
                }
            }
        } catch (exception $e) {

            Mage::logException($e);
        }

        return $addressData;
    }


    /**
     * reset customer password public method
     *
     * internal validations need to do
     *
     * @param $params
     *
     * @return bool|null|SimpleXMLElement[]
     */
    public function resetPassword($email, $lastName, $password)
    {
        try{

            $customerData = array('emailAddress'=> $email,
                                 'lastName' => $lastName,
                                 'newPassword'=>  $password
            );


            if(!empty($customerData['emailAddress'])
                && !empty($customerData['lastName'])
                    && !empty($customerData['newPassword'])){

                $result = $this->_resetPassword($customerData);

                if(!empty($result)){

                    return $result;
                }
            }


            return null;

        }catch (SoapFault $fault){

            $this->_log(array('SOAP Fault', $fault->faultstring));
        }catch (Exception $e){

            $this->_log($e->getMessage(), Zend_Log::ERR);
        }
    }


    /**
     *
     * direct attribute updates to avoid repeating observer calls
     *
     * @param $type
     * @param $entityId
     * @param $attributeCode
     * @param $value
     */
    public function customAttributeUpdate($type,$entityId,$attributeCode,$value)
    {
        $write  =  Mage::getSingleton('core/resource')->getConnection('write');

        $attribute = Mage::getModel('eav/entity_attribute')->loadByCode($type, $attributeCode);

        if($attribute){

            $attrTable = $attribute ->getBackend()->getTable();

            $cusTimedata = array(
                'entity_id'      => $entityId,
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id'   => $attribute->getId(),
                'value'          => $value
            );
            $write->insertOnDuplicate($attrTable, $cusTimedata, array('value'));
        }
    }
}