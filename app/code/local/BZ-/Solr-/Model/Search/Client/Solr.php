<?php

/**
 * Class Solr Description
 * @param 
 * @package Solr
 * @author Ben Zhang <ben_zhanghf@hotmail.com>
 */
class BZ_Solr_Model_Search_Client_Solr extends Apache_Solr_Service
{
    const SOLR_VERSION = '4.4';
    /**
     * Store user login, that needed in authentication with solr server
     *
     * @var string
     */
    protected $_login = '';

    /**
     * Store user password, that needed in authentication with solr server
     *
     * @var string
     */
    protected $_password = '';

    /**
     * Suggestions servlet
     */
    const SUGGESTIONS_SERVLET = 'spell';

    /**
     * Constructed servlet full path URLs
     *
     * @var string
     */
    protected $_suggestionsUrl;

    /**
     * Initialize Solr Client
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $transportInstance = new Apache_Solr_HttpTransport_Curl();
        // CurlNoReuse implementation instead creates and releases a cURL session for each request
        //$transportInstance = new Apache_Solr_HttpTransport_CurlNoReuse();
        $this->setHttpTransport($transportInstance);

        $_optionsNames = array(
            'hostname',
            'login',
            'password',
            'port',
            'path'
        );
        if (!sizeof(array_intersect($_optionsNames, array_keys($options)))) {
            Mage::throwException(Mage::helper('bz_solr')->__('Unable to perform search because of search engine missed configuration.'));
        }

        $this->setUserLogin($options['login']);
        $this->setPassword($options['password']);

        if (isset($options['timeout'])) {
            ini_set('default_socket_timeout', $options['timeout']);
        }

        parent::__construct($options['hostname'], $options['port'], '/' . $options['path'] . '/');
        return $this;
    }

    protected function _initUrls()
    {
        parent::_initUrls();
        $this->_suggestionsUrl = $this->_constructUrl(self::SUGGESTIONS_SERVLET);

    }

    /**
     * Send an rollback command.
     *
     * @param float $timeout Maximum expected duration of the commit operation on the server (otherwise, will throw a communication exception)
     * @return Apache_Solr_Response
     *
     * @throws Exception If an error occurs during the service call
     */
    public function rollback($timeout = 3600)
    {
        $rawPost = '<rollback/>';
        return $this->_sendRawPost($this->_updateUrl, $rawPost, $timeout);
    }

    /**
     * Create a delete document based on a multiple queries and submit it
     *
     * @param array $rawQueries Expected to be utf-8 encoded
     * @param boolean $fromPending
     * @param boolean $fromCommitted
     * @param float $timeout Maximum expected duration of the delete operation on the server (otherwise, will throw a communication exception)
     * @return Apache_Solr_Response
     *
     * @throws Exception If an error occurs during the service call
     */
    public function deleteByQueries($rawQueries, $fromPending = true, $fromCommitted = true, $timeout = 3600)
    {
        $pendingValue = $fromPending ? 'true' : 'false';
        $committedValue = $fromCommitted ? 'true' : 'false';

        $rawPost = '<delete fromPending="' . $pendingValue . '" fromCommitted="' . $committedValue . '">';

        foreach ($rawQueries as $query)
        {
            //escape special xml characters
            $query = htmlspecialchars($query, ENT_NOQUOTES, 'UTF-8');

            $rawPost .= '<query>' . $query . '</query>';
        }

        $rawPost .= '</delete>';

        return $this->delete($rawPost, $timeout);
    }

    /**
     * Alias to Apache_Solr_Service::deleteByMultipleIds() method
     *
     * @param array $ids Expected to be utf-8 encoded strings
     * @param boolean $fromPending
     * @param boolean $fromCommitted
     * @param float $timeout Maximum expected duration of the delete operation on the server (otherwise, will throw a communication exception)
     * @return Apache_Solr_Response
     *
     * @throws Exception If an error occurs during the service call
     */
    public function deleteByIds($ids, $fromPending = true, $fromCommitted = true, $timeout = 3600)
    {
        $this->deleteByMultipleIds($ids, $fromPending, $fromCommitted, $timeout);
    }

    /*protected function _sendRawGet($url, $timeout = FALSE)
    {
        //stream_context_set_option($this->_getContext, 'http', 'header', "Authorization: Basic " . base64_encode($this->getUserLogin() . ':' . $this->getPassword()));
        return parent::_sendRawGet($url, $timeout);
    }*/

    /**
     * Central method for making a post operation against this Solr Server
     *
     * @param string $url
     * @param string $rawPost
     * @param float $timeout Read timeout in seconds
     * @param string $contentType
     * @return Apache_Solr_Response
     *
     * @throws Exception If a non 200 response status is returned
     */
    protected function _sendRawPost($url, $rawPost, $timeout = FALSE, $contentType = 'text/xml; charset=UTF-8')
    {
        //stream_context_set_option($this->_postContext, 'http', 'header', "Authorization: Basic " . base64_encode($this->getUserLogin() . ':' . $this->getPassword()));
        //Mage::log($rawPost);
        return parent::_sendRawPost($url, $rawPost, $timeout, $contentType);
    }

    /**
     * Setter for solr server username
     *
     * @param string $username
     */
    public function setUserLogin($username)
    {
        $this->_login = (string)$username;
    }

    /**
     * Getter of solr server username
     *
     * @return string
     */
    public function getUserLogin()
    {
        return $this->_login;
    }

    /**
     * Setter for solr server password
     *
     * @param string $username
     */
    public function setPassword($username)
    {
        $this->_password = (string)$username;
    }

    /**
     * Getter of solr server password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

}
