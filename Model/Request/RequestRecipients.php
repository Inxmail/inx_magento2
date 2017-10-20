<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @copyright Copyright (c) 2017 Flagbit GmbH
 */

namespace Flagbit\Inxmail\Model\Request;

use Flagbit\Inxmail\Helper\Config;
use Flagbit\Inxmail\Model\Api\ApiClientFactory;

/**
 * Class RequestRecipients
 *
 * @package Flagbit\Inxmail\Model\Request
 */
class RequestRecipients extends AbstractRequest
{
    const REQUEST_PATH = 'recipients/';
    const REQUEST_ALL_ATTRIBUTES = '?allAttributes';

    const REQUEST_PARAMETER_ATTRIBUTES = 'attributes';
    const REQUEST_PARAMETER_SUBSCRIBED_TO = 'subscribedTo';
    const REQUEST_PARAMETER_EMAIL = 'email';

    /**
     * RequestRecipients constructor
     *
     * @param \Flagbit\Inxmail\Helper\Config $config
     * @param \Flagbit\Inxmail\Model\Api\ApiClientFactory $factory
     */
    public function __construct(Config $config, ApiClientFactory $factory)
    {
        parent::__construct($config, $factory);
    }

    /**
     * @return array
     */
    public function sendRequest(): array
    {
        $client = $this->getApiClient();
        $client->setCredentials($this->getCredentials());
        $client->setRequestPath(self::REQUEST_PATH . $this->_requestParam);
        $client->setRequestMethod(\Zend_Http_Client::GET);
        $client->setRequestUrl($this->_systemConfig->getApiUrl());

        $this->_response = $client->getResource('', '', null, null);

        return json_decode($this->_response, true);
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function requestWithAllAttributes(int $id): array
    {
        $this->_requestParam = implode('/', explode('/', $this->_requestParam));
        $this->_requestParam .= $id . self::REQUEST_ALL_ATTRIBUTES;
        return $this->sendRequest();
    }

    /**
     * @param int $id
     * @param array $attributes
     *
     * @return array
     */
    public function requestWithAttributes(int $id, array $attributes): array
    {
        $this->_requestParam = implode('/', explode('/', $this->_requestParam));
        $this->_requestParam .= $id . '?' . implode('&', $attributes);
        return $this->sendRequest();
    }

    /**
     * @param int $id
     *
     * @return int
     */
    public function deleteRequest(int $id): int
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
