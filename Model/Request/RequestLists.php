<?php
namespace Flagbit\Inxmail\Model\Request;

use Flagbit\Inxmail\Helper\Config;
use Flagbit\Inxmail\Model\Api\ApiClientFactory;

class RequestLists extends AbstractRequest
{
    const REQUEST_PATH = 'lists/';

    public function __construct(Config $config, ApiClientFactory $factory)
    {
        parent::__construct($config, $factory);
    }

    public function sendRequest()
    {
        $client = $this->getApiClient();
        $client->setCredentials($this->getCredentials());
        $client->setRequestPath(self::REQUEST_PATH.$this->requestParam);
        $client->setRequestMethod(\Zend_Http_Client::GET);
        $client->setRequestUrl($this->_systemConfig->getApiUrl());
        // ToDo: remove dryrun
        $this->response = $client->getResource('','',null,null, false);

        return json_decode($this->response, true);
    }

    public function writeRequest()
    {
        if (!empty($this->postData)) {
            $client = $this->getApiClient();
            $client->setCredentials($this->getCredentials());
            $client->setRequestPath(self::REQUEST_PATH . $this->requestParam);
            $client->setRequestMethod(\Zend_Http_Client::POST);
            $client->setRequestUrl($this->_systemConfig->getApiUrl());
            $client->setRequestData( is_array($this->_requestData) ? json_encode($this->_requestData) : $this->_requestData);
            // ToDo: remove dryrun
            $this->response = $client->postResource('', '', null, null, '');

            return $client->getResponseStatusCode();
        }

        return false;
    }

    public function deleteRequest(int $id)
    {
        $client = $this->getApiClient();
        $client->setCredentials($this->getCredentials());
        $client->setRequestPath(self::REQUEST_PATH . $this->requestParam);
        $client->setRequestMethod(\Zend_Http_Client::DELETE);
        $client->setRequestUrl($this->_systemConfig->getApiUrl());
        $client->setRequestData( is_array($this->_requestData) ? json_encode($this->_requestData) : $this->_requestData);
        $this->response = $client->deleteResource('', '', null, null, '');
        return $client->getResponseStatusCode();
    }

}
