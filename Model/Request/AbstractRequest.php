<?php

namespace Flagbit\Inxmail\Model\Request;

use Flagbit\Inxmail\Model\Api\ApiClient;
use \Flagbit\Inxmail\Model\Api\ApiClientFactory;
use \Flagbit\Inxmail\Model\Api\ApiClientInterface;
use \Flagbit\Inxmail\Model\Config\SystemConfig;
use \Flagbit\Inxmail\Helper\Config;

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
    protected $requestParam = '';
    /** @var string */
    protected $response = '';

    /*
      * "type" : "about:blank", / 404
     * "type" : "locked-resource", / 400
     * "type" : "invalid-request-body", / 400
     * "type" : "missing-parameter", / 400
     * "type" : "invalid-parameter-value", / 400
     * "type" : "missing-request-part", / 400
     * "type" : "type-mismatch", / 400
     * "type" : "unresolvable-request", / 400
     * "type" : "blacklisted", / 400
     * "type" : "subscription-error", / 400
     * "type" : "unsubscription-error", / 400
     *
     * "type" : "duplicate-resource", / 409
     * "type" : "about:blank", / 429
     *
     */

    const ERROR_CODES = array(404 => 'Not Found',400 => 'Bad Request', 429 => 'Too many requests', 409 => 'Conflict',
        401 => 'Unauthorized', 403 => 'Forbidden', 405 => 'Method Not Allowed', 406 => 'Not Acceptable',
        413 => 'Payload Too Large', 415 => 'Unsupported Media Type', 500 => 'Internal Server Error'
    );

    const STATUS_CODES = array(200 => 'OK', 201 => 'Created', 204 => 'No Content');

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

    protected function getApiClient(): ApiClientInterface
    {
        if (empty($this->_apiClient)) {
            $this->_apiClient = (new ApiClientFactory())->create(ApiClient::class);
        }

        return $this->_apiClient;
    }

    protected function getCredentials(): array
    {
        if (empty($this->_credentials)){
            $this->_credentials = array(
                $this->_systemConfig->getApiUser(), $this->_systemConfig->getApiKey()
            );
        }

        return $this->_credentials;
    }

    public function sendRequest()
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

    public function writeRequest()
    {
        $result = null;
        $client = $this->getApiClient();
        if ($client !== null && $client instanceof ApiClientInterface &&
            !empty($this->_requestData)) {
            $client->setRequestMethod(\Zend_Http_Client::POST);
            $result = $this->_apiClient->postResource($this->_systemConfig->getApiUrl(),
                self::REQUEST_PATH, $this->_requestHeader, $this->getCredentials(),
                is_array($this->_requestData) ? json_encode($this->_requestData) : $this->_requestData);
        }

        return json_decode($result, true);
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
        return $this->requestParam;
    }

    /**
     * @param string $requestParam
     */
    public function setRequestParam(string $requestParam)
    {
        $this->requestParam = $requestParam;
    }

    /**
     * @return string
     */
    public function getResponseJson(): string
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getResponseArray(): array
    {
        return json_decode($this->response, true);
    }

}
