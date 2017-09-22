<?php
// ToDo: don't forget
// The subscription response will return the recipient-ID of Inxmail. This ID should be saved.
namespace Flagbit\Inxmail\Model\Request;

use Flagbit\Inxmail\Helper\Config;
use Flagbit\Inxmail\Model\Api\ApiClientFactory;

class RequestSubscribeRecipients extends AbstractRequest
{
    const REQUEST_PATH = 'events/subscriptions/';
    const REQUEST_LIST_ID = 'listId';
    const REQUEST_START_DATE = 'startDate';
    const REQUEST_END_DATE = 'endDate';
    const REQUEST_TYPES = 'types';

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
        // {"listId":4,"email":"peter.lelewel@flagbit.de"}
        if (!empty($this->postData)) {
            $client = $this->getApiClient();
            $client->setCredentials($this->getCredentials());
            $client->setRequestPath(self::REQUEST_PATH . $this->requestParam);
            $client->setRequestMethod(\Zend_Http_Client::POST);
            $client->setRequestUrl($this->_systemConfig->getApiUrl());
            $client->setPostData( is_array($this->_requestData) ? json_encode($this->_requestData) : $this->requestData);
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
        $client->setRequestPath(self::REQUEST_PATH . $id);
        $client->setRequestMethod(\Zend_Http_Client::DELETE);
        $client->setRequestUrl($this->_systemConfig->getApiUrl());
        $client->setPostData( is_array($this->_requestData) ? json_encode($this->_requestData) : $this->requestData);
        $this->response = $client->deleteResource('', '', null, null, '');
        return $client->getResponseStatusCode();
    }

    public function requestWithAllAttributes(int $id) {
        $this->requestParam = implode('/',explode('/',$this->requestParam));
        $this->requestParam .= $id.self::REQUEST_ALL_ATTRIBUTES;
        return $this->sendRequest();
    }

    public function requestWithAttributes(int $id, array $attributes) {
        $this->requestParam = implode('/',explode('/',$this->requestParam));
        $this->requestParam .= $id.'?'.implode('&',$attributes);
        return $this->sendRequest();
    }
}
