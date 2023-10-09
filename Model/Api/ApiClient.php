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

namespace Flagbit\Inxmail\Model\Api;

use Flagbit\Inxmail\Exception\Api\InvalidArgumentException;
use Flagbit\Inxmail\Exception\Api\InvalidAuthenticationException;
use Flagbit\Inxmail\Exception\Api\MissingArgumentException;
use Magento\Framework\HTTP\Adapter\Curl;

/**
 * Class ApiClient
 * @package Flagbit\Inxmail\Model\Api
 */
class ApiClient implements ApiClientInterface
{
    /** @var  \Magento\Framework\HTTP\Adapter\Curl */
    protected $_requestClient;
    /** @var  \Flagbit\Inxmail\Model\Api\ApiClient */
    protected static $_apiClient;

    /**
     * @var array
     */
    protected $_header = [];
    /**
     * @var string
     */
    protected $_credentials = '';
    /**
     * @var string
     */
    protected $_requestUrl = '';
    /**
     * @var string
     */
    protected $_requestPath = '';
    /**
     * @var string
     */
    protected $_requestMethod = \Laminas\Http\Request::METHOD_POST;
    /**
     * @var string
     */
    protected $_postData = '';

    /** @var  array */
    protected $_responseHeader;
    /** @var  string */
    protected $_responseBody;
    /** @var  array */
    protected static $_responseInfo;
    /** @var  integer */
    protected static $_responseExpectedBytes;
    /** @var  integer */
    protected static $_responseCurrentBytes;
    /** @var  integer */
    protected static $_responseUploadExpectedBytes;
    /** @var  integer */
    protected static $_responseUploadCurrentBytes;
    /** @var  double */
    protected static $_responseContentLength;
    /** @var  integer */
    protected static $_responseCode;

    /** @var  resource */
    private static $requestObject;

    /**
     * Request allowed header accept types
     *
     * @var array
     */
    protected static $_allowedAcceptTypes = [
        'application/hal+json',
        'application/problem+json',
        'multipart/form-data',
        'multipart/mixed',
        'text/csv',
        'application/gzip'
    ];

    /**
     * Post request header types
     *
     * @var array
     */
    protected static $_allowedPostTypes = [
        'default' => 'application/hal+json',
        'fallback' => 'application/json'
    ];

    /**
     * Request allowed methods
     *
     * @var array
     */
    protected $_allowedMethods = [
        \Laminas\Http\Request::METHOD_GET, \Laminas\Http\Request::METHOD_POST, \Laminas\Http\Request::METHOD_PUT, \Laminas\Http\Request::METHOD_DELETE
    ];

    /**
     * Provide default header for get requests
     *
     * @var array
     */
    protected $_defaultHeader = [
        'Accept: application/hal+json,application/problem+json'
    ];

    /**
     * Provide default header for post/put/delete requests
     *
     * @var array
     */
    protected $_defaultPostHeader = [
        'Accept: application/hal+json,application/problem+json',
        'Content-type: application/hal+json;charset=UTF-8'
    ];

    /**
     * ApiClient constructor.
     */
    protected function __construct()
    {
        $this->_requestClient = new Curl();
    }

    /**
     * Implements singleton
     *
     * @return \Flagbit\Inxmail\Model\Api\ApiClient
     */
    public static function getApiClient(): self
    {
        if (self::$_apiClient === null) {
            self::$_apiClient = new self();
        }

        return self::$_apiClient;
    }


    /**
     * @param array|null $header
     */
    public function setHeader(array $header = null)
    {
        if (!empty($header)) {
            $this->_header = $header;
        } else if (empty($this->_header) || $this->_header === $this->_defaultPostHeader || $this->_header === $this->_defaultHeader) {
            switch ($this->_requestMethod) {
                case \Laminas\Http\Request::METHOD_GET:
                    $this->_header = $this->_defaultHeader;
                    break;
                case \Laminas\Http\Request::METHOD_DELETE:
                    $this->_header = $this->_defaultHeader;
                    break;
                case \Laminas\Http\Request::METHOD_POST:
                    $this->_header = $this->_defaultPostHeader;
                    break;
                case \Laminas\Http\Request::METHOD_PUT:
                    $this->_header = $this->_defaultPostHeader;
                    break;
            }
        }
    }

    /**
     * @param string $method
     *
     * @throws \Flagbit\Inxmail\Exception\Api\InvalidArgumentException
     */
    public function setRequestMethod(string $method)
    {
        if (in_array($method, $this->_allowedMethods, true)) {
            $this->_requestMethod = $method;
        } else {
            throw new InvalidArgumentException(__('Parameter for method not allowed'));
        }
    }

    /**
     * Provide credentials for API
     *
     * Can be omitted when provided to request function. Must not be empty.
     *
     * @param array $credentials
     *
     * @throws \Flagbit\Inxmail\Exception\Api\InvalidArgumentException
     */
    public function setCredentials(array $credentials)
    {
        $paramCnt = count($credentials);
        $fail = false;

        if ($paramCnt === 1) {
            if (!empty(trim($credentials[array_keys($credentials)[0]]))) {
                $this->_credentials = trim(array_shift($credentials));
            } else {
                $fail = true;
            }
        } else if ($paramCnt === 2) {
            if (!empty($credentials[array_keys($credentials)[0]]) && !empty($credentials[array_keys($credentials)[1]])) {
                $this->_credentials = trim(implode(':', $credentials));
            } else {
                $fail = true;
            }
        } else {
            $fail = true;
        }

        if ($fail) {
            throw new InvalidArgumentException(__('Parameters cannot be parsed'));
        }
    }

    /**
     * Set the appropriate request Url
     *
     * Setting the url can be omitted when given to the request method itself
     *
     * @param string $requestUrl
     *
     * @throws \Flagbit\Inxmail\Exception\Api\InvalidArgumentException
     */
    public function setRequestUrl(string $requestUrl)
    {
        $url = trim($requestUrl);
        $uri = new \Laminas\Uri\Uri($url);
        if ($uri->isValid() && $this->validateProtocol($url)) {
            $url .= (substr($url, strlen($url) - 1) === '/') ? '' : '/';
            $this->_requestUrl = $url;
        } else {
            throw new InvalidArgumentException(__('Url is not valid'));
        }
    }

    /**
     * Set request path
     *
     * Setting the request path may be omitted when provided on request method itself
     *
     * @param string $requestPath
     *
     * @throws \Flagbit\Inxmail\Exception\Api\InvalidArgumentException
     */
    public function setRequestPath(string $requestPath)
    {
        $path = trim($requestPath);
        if (!empty($path)) {
//            $path = (strpos($path, '/') === 0) ? substr($path, 1) : $path;
//            $path .= (strpos($path, '/') === (strlen($path)-1)) ? '' : '/';

            $this->_requestPath = $path;
        } else {
            throw new InvalidArgumentException(__('Path is not valid'));
        }
    }

    /**
     * @return string
     */
    public function getPostData(): string
    {
        return $this->_postData;
    }

    /**
     * @param string $postData
     */
    public function setPostData(string $postData)
    {
        $this->_postData = $postData;
    }

    /**
     * Get resource answer from server
     *
     * @param string $requestUrl
     * @param string $requestPath
     * @param string|null $header
     * @param array|null $credentials
     *
     * @return bool|string
     *
     * @throws \Flagbit\Inxmail\Exception\Api\InvalidAuthenticationException
     * @throws \Flagbit\Inxmail\Exception\Api\MissingArgumentException
     * @throws \Flagbit\Inxmail\Exception\Api\InvalidArgumentException
     */
    public function getResource(
        string $requestUrl = '', string $requestPath = '',
        string $header = null, array $credentials = null)
    {
        if (!empty($requestUrl) || !empty($this->_requestUrl)) {
            if (empty($this->_requestUrl)) {
                $this->setRequestUrl($requestUrl);
            }

            if (!empty($requestPath)) {
                $this->setRequestPath($requestPath);
            }

            if ($this->_requestMethod !== \Laminas\Http\Request::METHOD_GET && $this->_requestMethod !== \Laminas\Http\Request::METHOD_DELETE) {
                $this->setRequestMethod(\Laminas\Http\Request::METHOD_GET);
            }

            $this->setHeader($header);

            $requestHeader = $this->_header;
            if (!empty($credentials)) {
                $this->setCredentials($credentials);
                if (!in_array('Authorization: Basic ' . base64_encode($this->_credentials), $this->_header, true)) {
                    $requestHeader[]  = 'Authorization: Basic ' . base64_encode($this->_credentials);
                }
            } else if (!empty($this->_credentials)) {
                if (!in_array('Authorization: Basic ' . base64_encode($this->_credentials), $this->_header, true)) {
                    $requestHeader[] = 'Authorization: Basic ' . base64_encode($this->_credentials);
                }
            } else {
                throw new InvalidAuthenticationException(__('Credentials not provided'));
            }

            if ($this->_requestMethod === \Laminas\Http\Request::METHOD_DELETE) {
                $this->_requestClient->addOption(CURLOPT_CUSTOMREQUEST, $this->_requestMethod);
                $this->_requestClient->addOption(CURLOPT_RETURNTRANSFER, true);
            }

            $this->_requestClient->addOption(CURLOPT_PROGRESSFUNCTION, '\Flagbit\Inxmail\Model\Api\ApiClient::setResponseInformation');
            $this->_requestClient->addOption(CURLOPT_NOPROGRESS, FALSE);
            $this->_requestClient->write(
                $this->_requestMethod,
                $this->_requestUrl . $this->_requestPath,
                '1.1',
                $requestHeader
            );

            $response = $this->_requestClient->read();
            $this->_responseBody = \Laminas\Http\Response::fromString($response)->getBody();
            $this->_responseHeader = \Laminas\Http\Response::fromString($response)->getHeaders();

            return $this->_responseBody;
        } else {
            throw new MissingArgumentException(__('URL Parameter missing'));
        }
    }

    /**
     * Post data to server
     *
     * @param string $requestUrl
     * @param string $requestPath
     * @param string|null $header
     * @param array|null $credentials
     * @param string $postData
     *
     * @return bool|string
     *
     * @throws \Flagbit\Inxmail\Exception\Api\InvalidAuthenticationException
     * @throws \Flagbit\Inxmail\Exception\Api\MissingArgumentException
     */
    public function postResource(
        string $requestUrl = '', string $requestPath = '',
        string $header = null, array $credentials = null, string $postData = ''
    )
    {
        if ((!empty($requestUrl) || !empty($this->_requestUrl)) && (!empty($this->_postData) || !empty($postData))) {
            if (empty($this->_requestUrl)) {
                $this->setRequestUrl($requestUrl);
            }

            if (!empty($requestPath)) {
                $this->setRequestPath($requestPath);
            }

            if (!empty($postData)) {
                $this->setPostData($postData);
            }

            if ($this->_requestMethod !== \Laminas\Http\Request::METHOD_POST && $this->_requestMethod !== \Laminas\Http\Request::METHOD_PUT) {
                $this->setRequestMethod(\Laminas\Http\Request::METHOD_POST);
            }

            $this->setHeader($header);

            $requestHeader = $this->_header;
            if (!empty($credentials)) {
                $this->setCredentials($credentials);
                if (!in_array('Authorization: Basic'  . base64_encode($this->_credentials), $this->_header, true)) {
                    $requestHeader[] = 'Authorization: Basic ' . base64_encode($this->_credentials);
                }
            } else if (!empty($this->_credentials)) {
                if (!in_array('Authorization: Basic '  . base64_encode($this->_credentials), $this->_header, true)) {
                    $requestHeader[] = 'Authorization: Basic ' . base64_encode($this->_credentials);
                }
            } else {
                throw new InvalidAuthenticationException(__('Credentials not provided'));
            }

            $this->_requestClient->addOption(CURLOPT_PROGRESSFUNCTION, '\Flagbit\Inxmail\Model\Api\ApiClient::setResponseInformation');
            $this->_requestClient->addOption(CURLOPT_NOPROGRESS, FALSE);
            $this->_requestClient->addOption(CURLINFO_HEADER_OUT, true);

            if ($this->_requestMethod === \Laminas\Http\Request::METHOD_PUT) {
                $this->_requestClient->addOption(CURLOPT_CUSTOMREQUEST, $this->_requestMethod);
                $this->_requestClient->addOption(CURLOPT_POSTFIELDS, $this->_postData);
                $this->_requestClient->addOption(CURLOPT_RETURNTRANSFER, true);
            }

            $url = $this->_requestUrl . $this->_requestPath;

            $this->_requestClient->write(
                $this->_requestMethod,
                $url,
                '1.1',
                $requestHeader,
                $this->_postData
            );

            $response = $this->_requestClient->read();

            $this->_responseBody = \Laminas\Http\Response::fromString($response)->getBody();
            $this->_responseHeader = \Laminas\Http\Response::fromString($response)->getHeaders();

            return $this->_responseBody;
        } else {
            throw new MissingArgumentException(__('URL Parameter missing or no data to post/put'));
        }
    }

    /**
     * Put data to server
     *
     * @param string $requestUrl
     * @param string $requestPath
     * @param string|null $header
     * @param array|null $credentials
     * @param string $postData
     *
     * @return bool|string
     */
    public function putResource(
        string $requestUrl = '', string $requestPath = '',
        string $header = null, array $credentials = null, string $postData = ''
    ){
        $this->setRequestMethod(\Laminas\Http\Request::METHOD_PUT);
        return $this->postResource($requestUrl, $requestPath, $header, $credentials, $postData);
    }

    /**
     * Delete request to server
     *
     * @param string $requestUrl
     * @param string $requestPath
     * @param string|null $header
     * @param array|null $credentials
     *
     * @return bool|string
     */
    public function deleteResource(
        string $requestUrl = '', string $requestPath = '',
        string $header = null, array $credentials = null
    )
    {
        $this->setRequestMethod(\Laminas\Http\Request::METHOD_DELETE);
        return $this->getResource($requestUrl, $requestPath, $header, $credentials);
    }

    /**
     * Callback for curl processing
     *
     * @param $curl
     * @param int $expected
     * @param int $current
     * @param int $uploadExpected
     * @param int $currentUpload
     *
     * @return int
     */
    public static function setResponseInformation($curl, int $expected, int $current, int $uploadExpected, int $currentUpload): int
    {
        self::$_responseContentLength = curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        self::$_responseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        self::$_responseInfo = curl_getinfo($curl);
        self::$_responseExpectedBytes = $expected;
        self::$_responseCurrentBytes = $current;
        self::$_responseUploadExpectedBytes = $uploadExpected;
        self::$_responseUploadCurrentBytes = $currentUpload;
        self::$requestObject = &$curl;
        return 0;
    }

    /**
     * @return array
     */
    public function getResponseHeader(): array
    {
        return $this->_responseHeader;
    }

    /**
     * @return string
     */
    public function getResponseBody(): string
    {
        return $this->_responseBody;
    }

    /**
     * @return string
     */
    public function getResponseContentType(): string
    {
        return self::$_responseInfo['content_type'];
    }

    /**
     * @return int
     */
    public function getResponseStatusCode(): int
    {
        return (int)self::$_responseInfo['http_code'];
    }

    /**
     * @return array
     */
    public function getResponseInfo(): array
    {
        return self::$_responseInfo;
    }

    /**
     * Only http and https are allowed
     *
     * @param string $url
     * @return bool
     */
    private function validateProtocol(string $url): bool
    {
        $test = explode(':', $url);
        return (count($test) > 1 && in_array(strtolower($test[0]), ['http', 'https'], true));
    }


}
