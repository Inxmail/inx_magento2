<?php
namespace Flagbit\Inxmail\Model\Request;

use Flagbit\Inxmail\Helper\Config;
use Flagbit\Inxmail\Model\Api\ApiClientFactory;

class RequestRecipients extends AbstractRequest
{
    const REQUEST_PATH = 'recipients/';
    const REQUEST_ALL_ATTRIBUTES = '?allAttributes';

    const REQUEST_PARAMETER_ATTRIBUTES = 'attributes';
    const REQUEST_PARAMETER_SUBSCRIBED_TO = 'subscribedTo';
    const REQUEST_PARAMETER_EMAIL = 'email';

    public function __construct(Config $config, ApiClientFactory $factory)
    {
        parent::__construct($config, $factory);
    }

    public function sendRequest()
    {
        $client = $this->getApiClient();
        $client->setCredentials($this->getCredentials());
        $client->setRequestPath(self::REQUEST_PATH.$this->_requestParam);
        $client->setRequestMethod(\Zend_Http_Client::GET);
        $client->setRequestUrl($this->_systemConfig->getApiUrl());
        // ToDo: remove dryrun
        $this->_response = $client->getResource('','',null,null, false);

        return json_decode($this->_response, true);
    }

    public function requestWithAllAttributes(int $id) {
        $this->_requestParam = implode('/',explode('/',$this->_requestParam));
        $this->_requestParam .= $id.self::REQUEST_ALL_ATTRIBUTES;
        return $this->sendRequest();
    }

    public function requestWithAttributes(int $id, array $attributes) {
        $this->_requestParam = implode('/',explode('/',$this->_requestParam));
        $this->_requestParam .= $id.'?'.implode('&',$attributes);
        return $this->sendRequest();
    }

    public function deleteRequest(int $id)
    {
        $returnValue = false;

        if (!empty($id)) {
            $client = $this->getApiClient();
            $client->setCredentials($this->getCredentials());
            $client->setRequestPath(self::REQUEST_PATH . $id);
            $client->setRequestMethod(\Zend_Http_Client::DELETE);
            $client->setRequestUrl($this->_systemConfig->getApiUrl());
            $this->_response = $client->deleteResource('', '', null, null, '', false);
            $returnValue = $client->getResponseStatusCode();
        }
        return $returnValue;
    }
}
