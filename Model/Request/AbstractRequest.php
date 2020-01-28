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
use Flagbit\Inxmail\Model\Api\ApiClient;
use Flagbit\Inxmail\Model\Api\ApiClientFactory;
use Flagbit\Inxmail\Model\Api\ApiClientInterface;
use Flagbit\Inxmail\Model\Config\SystemConfig;

/**
 * Class AbstractRequest
 *
 * @package Flagbit\Inxmail\Model\Request
 */
class AbstractRequest implements RequestInterface
{
    const REQUEST_PATH = '';

    /** @var string */
    protected $_requestUrl = '';
    /** @var  array */
    protected $_requestData = array();
    /** @var ApiClientInterface */
    protected $_apiClient;
    /** @var \Flagbit\Inxmail\Model\Config\SystemConfig */
    protected $_systemConfig;
    /** @var string */
    protected $_requestHeader = '';
    /** @var array */
    protected $_credentials = array();
    /** @var string */
    protected $_requestParam = '';
    /** @var string */
    protected $_response = '';

    const ERROR_CODES = array(
        400 => 'Bad Request', 401 => 'Unauthorized', 403 => 'Forbidden',
        404 => 'Not Found', 405 => 'Method Not Allowed', 406 => 'Not Acceptable',
        409 => 'Conflict', 413 => 'Payload Too Large', 415 => 'Unsupported Media Type',
        429 => 'Too many requests', 500 => 'Internal Server Error'
    );

    const STATUS_CODES = array(200 => 'OK', 201 => 'Created', 204 => 'No Content');

    /**
     * AbstractRequest constructor
     *
     * @param Config $config
     * @param use \Flagbit\Inxmail\Model\Api\ApiClientFactory $factory
     */
    public function __construct(Config $config, ApiClientFactory $factory)
    {
        $this->_systemConfig = SystemConfig::getSystemConfig($config);
        $this->_apiClient = $factory->create(ApiClient::class);

    }

    /**
     * @return array
     */
    public function getRequestData(): array
    {
        if (is_array($this->_requestData)) {
            return $this->_requestData;
        }
        return json_decode($this->_requestData, true);
    }

    /**
     * @param string $requestData
     */
    public function setRequestData(string $requestData)
    {
        $this->_requestData = $requestData;
    }

    /**
     * @return string
     */
    public function getRequestUrl(): string
    {
        return $this->_requestUrl;
    }

    /**
     * @param string $_requestUrl
     */
    public function setRequestUrl(string $_requestUrl)
    {
        $this->_requestUrl = $_requestUrl;
    }

    /**
     * @param array $params
     */
    public function addRequestData(array $params)
    {
        $this->_requestData[] = $params;
    }

    /**
     * @return \Flagbit\Inxmail\Model\Api\ApiClientInterface
     */
    protected function getApiClient(): ApiClientInterface
    {
        if (empty($this->_apiClient)) {
            $this->_apiClient = (new ApiClientFactory())->create(ApiClient::class);
        }

        return $this->_apiClient;
    }

    /**
     * @return array
     */
    protected function getCredentials(): array
    {
        if (empty($this->_credentials)) {
            $this->_credentials = array(
                $this->_systemConfig->getApiUser(), $this->_systemConfig->getApiKey()
            );
        }

        return $this->_credentials;
    }

    /**
     * @return array
     */
    public function sendRequest(): array
    {
        $result = null;
        $client = $this->getApiClient();
        if ($client !== null && $client instanceof ApiClientInterface) {
            $client->setRequestMethod(\Zend_Http_Client::GET);
            $result = $this->_apiClient->getResource($this->_systemConfig->getApiUrl(),
                self::REQUEST_PATH, $this->_requestHeader, $this->getCredentials());
        }

        return json_decode($result, true);
    }

    /**
     * @return int
     */
    public function writeRequest(): int
    {
        return 0;
    }

    /**
     * @param int $id
     *
     * @return int
     */
    public function putRequest(int $id): int
    {
        return 0;
    }

    /**
     * @param int $id
     *
     * @return int
     */
    public function deleteRequest(int $id): int
    {
        return 0;
    }

    /**
     * @return array
     */
    public function getResponseHeader(): array
    {
        return $this->getApiClient()->getResponseHeader();
    }

    /**
     * @return int
     */
    public function getResponseCode(): int
    {
        return $this->getApiClient()->getResponseStatusCode();
    }

    /**
     * @return string
     */
    public function getRequestParam(): string
    {
        return $this->_requestParam;
    }

    /**
     * @param string $requestParam
     */
    public function setRequestParam(string $requestParam)
    {
        $this->_requestParam = $requestParam;
    }

    /**
     * @return string
     */
    public function getResponseJson(): string
    {
        return $this->_response;
    }

    /**
     * @return array
     */
    public function getResponseArray(): array
    {
        return json_decode($this->_response, true);
    }
}
