<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @author Flagbit GmbH
 * @copyright Copyright © 2017-2018 Inxmail GmbH
 * @license Licensed under the Open Software License version 3.0 (https://opensource.org/licenses/OSL-3.0)
 *
 */

namespace Flagbit\Inxmail\Model\Request;

use Flagbit\Inxmail\Helper\Config;
use Flagbit\Inxmail\Model\Api\ApiClientFactory;

/**
 * Class RequestLists
 *
 * @package Flagbit\Inxmail\Model\Request
 */
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

    /**
     * RequestLists constructor
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
        $client->setRequestMethod(\Laminas\Http\Request::METHOD_GET);
        $client->setRequestUrl($this->_systemConfig->getApiUrl());
        $this->_response = $client->getResource('', '', null, null);

        return json_decode($this->_response, true);
    }

    /**
     * @return int
     */
    public function writeRequest(): int
    {
        if (!empty($this->_requestData)) {
            $client = $this->getApiClient();
            $client->setCredentials($this->getCredentials());
            $client->setRequestPath(self::REQUEST_PATH . $this->_requestParam);
            $client->setRequestMethod(\Laminas\Http\Request::METHOD_POST);
            $client->setRequestUrl($this->_systemConfig->getApiUrl());
            $client->setPostData(is_array($this->_requestData) ? json_encode($this->_requestData) : $this->_requestData);
            $this->_response = $client->postResource('', '', null, null, '');

            return $client->getResponseStatusCode();
        }

        return 0;
    }

    /**
     * @param int $id
     *
     * @return int
     */
    public function putRequest(int $id): int
    {
        if (!empty($id)) {
            $client = $this->getApiClient();
            $client->setCredentials($this->getCredentials());
            $client->setRequestPath(self::REQUEST_PATH . $id);
            $client->setRequestMethod(\Laminas\Http\Request::METHOD_PUT);
            $client->setRequestUrl($this->_systemConfig->getApiUrl());
            $client->setPostData(is_array($this->_requestData) ? json_encode($this->_requestData) : $this->_requestData);
            $this->_response = $client->putResource('', '', null, null, '');

            return $client->getResponseStatusCode();
        }

        return 0;
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
            $client->setRequestMethod(\Laminas\Http\Request::METHOD_DELETE);
            $client->setRequestUrl($this->_systemConfig->getApiUrl());
            $this->_response = $client->deleteResource('', '', null, null);
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
        return [
            self::PARAMETER_NAME => '',
            self::PARAMETER_TYPE => self::LIST_TYPE_STANDARD,
            self::PARAMETER_SENDER_ADDRESS => ''
        ];
    }
}
