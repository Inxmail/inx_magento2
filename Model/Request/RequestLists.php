<?php
namespace Flagbit\Inxmail\Model\Request;

use Flagbit\Inxmail\Helper\Config;
use Flagbit\Inxmail\Model\Api\ApiClientFactory;

class RequestLists extends AbstractRequest
{
    const REQUEST_PATH = 'lists/';

    const LIST_TYPE_STANDARD = 'STANDARD';

    const PARAMETER_NAME = 'name';
    const PARAMETER_TYPE = 'type';
    const PARAMETER_SENDER_ADDRESS = 'senderAddress';
    const PARAMETER_SENDER_NAME = 'senderName';
    const PARAMETER_REPLY_TO_ADDRESS = 'replyToAddress';
    const PARAMETER_REPLAY_TO_NAME = 'replyToName';
    const PARAMETER_DESCRIPTION = 'description';

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
        $this->_response = $client->getResource('','',null,null, false);

        return json_decode($this->_response, true);
    }

    public function writeRequest()
    {
        if (!empty($this->_requestData)) {
            $client = $this->getApiClient();
            $client->setCredentials($this->getCredentials());
            $client->setRequestPath(self::REQUEST_PATH . $this->_requestParam);
            $client->setRequestMethod(\Zend_Http_Client::POST);
            $client->setRequestUrl($this->_systemConfig->getApiUrl());
            $client->setPostData( is_array($this->_requestData) ? json_encode($this->_requestData) : $this->_requestData);
            $this->_response = $client->postResource('', '', null, null, '', false);

            var_dump($this->_response);
            return $client->getResponseStatusCode();
        }

        return false;
    }

    public function putRequest(int $id) {
        if (!empty($id)) {
            $client = $this->getApiClient();
            $client->setCredentials($this->getCredentials());
            $client->setRequestPath(self::REQUEST_PATH . $id);
            $client->setRequestMethod(\Zend_Http_Client::PUT);
            $client->setRequestUrl($this->_systemConfig->getApiUrl());
            $client->setPostData( is_array($this->_requestData) ? json_encode($this->_requestData) : $this->_requestData);
            $this->_response = $client->putResource('', '', null, null, '', false);

            return $client->getResponseStatusCode();
        }

        return false;
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

    /**
     * Returns minimal valid array for new list
     *
     * @return array
     */
    public function getStandardListOptions(): array
    {
        return array(
            self::PARAMETER_NAME => '',
            self::PARAMETER_TYPE => self::LIST_TYPE_STANDARD,
            self::PARAMETER_SENDER_ADDRESS => ''
        );
    }
}
