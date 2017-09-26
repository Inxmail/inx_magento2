<?php

namespace Flagbit\Inxmail\Model\Api;

use \Flagbit\Inxmail\Exception\Api\MissingArgumentException;
use \Flagbit\Inxmail\Exception\Api\InvalidArgumentException;
use \Flagbit\Inxmail\Exception\Api\InvalidAuthenticationException;
use \Magento\Framework\HTTP\Adapter\Curl;
use \Zend_Uri;

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
    protected $_header = array();
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
    protected $_requestMethod = \Zend_Http_Client::POST;
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
     * Request accept header types
     *
     * @var array
     */
    protected $_allowedAcceptTypes = array(
        'application/hal+json',
        'application/problem+json',
        'multipart/form-data',
        'multipart/mixed',
        'text/csv',
        'application/gzip'

    );

    /**
     * Post request header types
     *
     * @var array
     */
    protected $_allowedPostTypes = array(
        'default' => 'application/hal+json',
        'fallback' => 'application/json'
    );

    /**
     * Request allowed methods
     *
     * @var array
     */
    protected $_allowedMethods = array(
        \Zend_Http_Client::GET, \Zend_Http_Client::POST, \Zend_Http_Client::PUT, \Zend_Http_Client::DELETE
    );

    /**
     * Provide default header for get requests
     *
     * @var array
     */
    protected $_defaultHeader = array(
        'Accept: application/hal+json,application/problem+json'
    );

    /**
     * Provide default header for post/put/delete requests
     * @var array
     */
    protected $_defaultPostHeader = array(
        'Accept: application/hal+json,application/problem+json',
        'Content-type: application/hal+json;charset=UTF-8'
    );

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
     * @return ApiClient
     */
    public static function getApiClient(): ApiClient
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
        } else if (empty($this->_header) || $this->_header == $this->_defaultPostHeader || $this->_header == $this->_defaultHeader) {
            switch ($this->_requestMethod) {
                case \Zend_Http_Client::GET:
                    $this->_header = $this->_defaultHeader;
                    break;
                case \Zend_Http_Client::DELETE:
                    $this->_header = $this->_defaultHeader;
                    break;
                case \Zend_Http_Client::POST:
                    $this->_header = $this->_defaultPostHeader;
                    break;
                case \Zend_Http_Client::PUT:
                    $this->_header = $this->_defaultPostHeader;
                    break;
            }
        }
    }

    /**
     * @param string $method
     * @throws InvalidArgumentException
     */
    public function setRequestMethod(string $method)
    {
        if (in_array($method, $this->_allowedMethods)) {
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
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
     */
    public function setRequestUrl(string $requestUrl)
    {
        $url = trim($requestUrl);
        if (Zend_Uri::check($url) && $this->validateProtocol($url)) {
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
     * @throws InvalidArgumentException
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
     * @param bool $dryrun
     * @return bool|string
     * @throws InvalidAuthenticationException
     * @throws MissingArgumentException
     * @throws InvalidArgumentException
     */
    public function getResource(
        string $requestUrl = '', string $requestPath = '',
        string $header = null, array $credentials = null, bool $dryrun = true)
    {
        if (!empty($requestUrl) || !empty($this->_requestUrl)) {
            if (empty($this->_requestUrl)) {
                $this->setRequestUrl($requestUrl);
            }

            if (!empty($requestPath)) {
                $this->setRequestPath($requestPath);
            }

            if ($this->_requestMethod !== \Zend_Http_Client::GET && $this->_requestMethod !== \Zend_Http_Client::DELETE) {
                $this->setRequestMethod(\Zend_Http_Client::GET);
            }

            $this->setHeader($header);

            $requestHeader = $this->_header;
            if (!empty($credentials)) {
                $this->setCredentials($credentials);
                if (!in_array('Authorization: Basic ' . base64_encode($this->_credentials), $this->_header)) {
                    $requestHeader[]  = 'Authorization: Basic ' . base64_encode($this->_credentials);
                }
            } else if (!empty($this->_credentials)) {
                if (!in_array('Authorization: Basic ' . base64_encode($this->_credentials), $this->_header)) {
                    $requestHeader[] = 'Authorization: Basic ' . base64_encode($this->_credentials);
                }
            } else {
                throw new InvalidAuthenticationException(__('Credentials not provided'));
            }

            if ($this->_requestMethod === \Zend_Http_Client::DELETE) {
                $this->_requestClient->addOption(CURLOPT_CUSTOMREQUEST, $this->_requestMethod);
                $this->_requestClient->addOption(CURLOPT_RETURNTRANSFER, true);
            }

            $this->_requestClient->addOption(CURLOPT_PROGRESSFUNCTION, '\Flagbit\Inxmail\Model\Api\ApiClient::setResponseInformation');
            $this->_requestClient->addOption(CURLOPT_NOPROGRESS, FALSE);
            $this->_requestClient->write(
                $this->_requestMethod,
                $this->_requestUrl . $this->_requestPath,
                \Zend_Http_Client::HTTP_1,
                $requestHeader
            );

            // ToDo: activate for real server testing
            if (!$dryrun) {
                var_dump("real: " . $this->_requestUrl . $this->_requestPath);
                $response = $this->_requestClient->read();
                $this->_responseBody = \Zend_Http_Response::extractBody($response);
                $this->_responseHeader = \Zend_Http_Response::extractHeaders($response);
            } else {
                $response = $this->getTestResponse();
                $this->_responseHeader = substr($response, 0, strlen($response) - self::$_responseCurrentBytes);
                $this->_responseBody = substr($response, strlen($response) - self::$_responseCurrentBytes);
            }

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
     * @param bool $dryrun
     * @return bool|string
     * @throws InvalidAuthenticationException
     * @throws MissingArgumentException
     */
    public function postResource(
        string $requestUrl = '', string $requestPath = '',
        string $header = null, array $credentials = null, string $postData = '', bool $dryrun = true
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

            if ($this->_requestMethod !== \Zend_Http_Client::POST && $this->_requestMethod !== \Zend_Http_Client::PUT) {
                $this->setRequestMethod(\Zend_Http_Client::POST);
            }

            $this->setHeader($header);

            $requestHeader = $this->_header;
            if (!empty($credentials)) {
                $this->setCredentials($credentials);
                if (!in_array('Authorization: Basic'  . base64_encode($this->_credentials), $this->_header)) {
                    $requestHeader[] = 'Authorization: Basic ' . base64_encode($this->_credentials);
                }
            } else if (!empty($this->_credentials)) {
                if (!in_array('Authorization: Basic '  . base64_encode($this->_credentials), $this->_header)) {
                    $requestHeader[] = 'Authorization: Basic ' . base64_encode($this->_credentials);
                }
            } else {
                throw new InvalidAuthenticationException(__('Credentials not provided'));
            }

            $this->_requestClient->addOption(CURLOPT_PROGRESSFUNCTION, '\Flagbit\Inxmail\Model\Api\ApiClient::setResponseInformation');
            $this->_requestClient->addOption(CURLOPT_NOPROGRESS, FALSE);
            $this->_requestClient->addOption(CURLINFO_HEADER_OUT, true);

            if ($this->_requestMethod === \Zend_Http_Client::PUT) {
                $this->_requestClient->addOption(CURLOPT_CUSTOMREQUEST, $this->_requestMethod);
                $this->_requestClient->addOption(CURLOPT_POSTFIELDS, $this->_postData);
                $this->_requestClient->addOption(CURLOPT_RETURNTRANSFER, true);
            }

            $url = $this->_requestUrl . $this->_requestPath;
            $this->_requestClient->write(
                $this->_requestMethod,
                $url,
                \Zend_Http_Client::HTTP_1,
                $requestHeader,
                $this->_postData
            );
            var_dump($url, $this->_postData, $this->_header);
            // ToDo: activate for real server testing
            if (!$dryrun) {
                var_dump("real: " . $url);

                $response = $this->_requestClient->read();
            } else {
                $response = $this->getTestResponse();
            }

            $this->_responseBody = \Zend_Http_Response::extractBody($response);
            $this->_responseHeader = \Zend_Http_Response::extractHeaders($response);
            return $this->_responseBody;
        } else {
            throw new MissingArgumentException(__('URL Parameter missing or no data to post/put'));
        }
    }

    /**
     * @param string $requestUrl
     * @param string $requestPath
     * @param string|null $header
     * @param array|null $credentials
     * @param string $postData
     * @param bool $dryrun
     * @return bool|string
     */
    public function putResource(
        string $requestUrl = '', string $requestPath = '',
        string $header = null, array $credentials = null, string $postData = '', bool $dryrun = true
    ){
        $this->setRequestMethod(\Zend_Http_Client::PUT);
        return $this->postResource($requestUrl, $requestPath, $header, $credentials, $postData, $dryrun);
    }

    /**
     * @param string $requestUrl
     * @param string $requestPath
     * @param string|null $header
     * @param array|null $credentials
     * @param bool $dryrun
     * @return bool|string
     */
    public function deleteResource(
        string $requestUrl = '', string $requestPath = '',
        string $header = null, array $credentials = null, $dryrun = true
    )
    {
        $this->setRequestMethod(\Zend_Http_Client::DELETE);
        return $this->getResource($requestUrl, $requestPath, $header, $credentials, $dryrun);
    }

    /**
     * Callback for curl processing
     *
     * @param $curl
     * @param int $expected
     * @param int $current
     * @param int $uploadExpected
     * @param int $currentUpload
     * @return int
     */
    public static function setResponseInformation($curl, int $expected, int $current, int $uploadExpected, int $currentUpload): int
    {
//        curl_getinfo($curl, CURLINFO_HTTP_CODE);
//        curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
//        curl_getinfo($curl, CURLINFO_HEADER_SIZE);
//        curl_getinfo($curl, CURLINFO_TOTAL_TIME);
        self::$_responseContentLength = curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        self::$_responseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        self::$_responseInfo = curl_getInfo($curl);
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
        var_dump("status code");
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
     * @return string
     */
    private function getTestResponse(): string
    {
        $this->setTestResponseData();
        return 'HTTP/1.1 200
Date: Tue, 19 Sep 2017 12:31:53 GMT
Server: Apache
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Cache-Control: no-cache, no-store, max-age=0, must-revalidate
Pragma: no-cache
Expires: 0
X-Frame-Options: DENY
X-RateLimit-Limit: 600
X-RateLimit-Remaining: 598
X-RateLimit-Reset: 34
Content-Type: application/hal+json;charset=UTF-8
Set-Cookie: JSESSIONID=81F3CC56B9781BB9E8994E7B1DB28F27; Path=/inxmail3; HttpOnly

{"_links":{"inx:attributes":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/attributes"},"inx:recipients":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/recipients"},"inx:lists":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/lists"},"inx:recipient-imports":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/imports/recipients"},"inx:subscription-events":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/events/subscriptions"},"inx:unsubscription-events":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/events/unsubscriptions"},"inx:bounces":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/bounces"},"curies":[{"href":"https://apidocs.inxmail.com/xpro/rest/v1/relations/{rel}","name":"inx","templated":true}]}}';
    }

    /**
     * @return string
     */
    private function getTestResponseBody(): string
    {
        $this->setTestResponseData();
        return '{"_links":{"inx:attributes":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/attributes"},"inx:recipients":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/recipients"},"inx:lists":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/lists"},"inx:recipient-imports":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/imports/recipients"},"inx:subscription-events":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/events/subscriptions"},"inx:unsubscription-events":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/events/unsubscriptions"},"inx:bounces":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/bounces"},"curies":[{"href":"https://apidocs.inxmail.com/xpro/rest/v1/relations/{rel}","name":"inx","templated":true}]}}';
    }

    /**
     * @return string
     */
    private function getTestResponseHeader():string
    {
        $this->setTestResponseData();
        return 'HTTP/1.1 200
Date: Tue, 19 Sep 2017 12:26:36 GMT
Server: Apache
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Cache-Control: no-cache, no-store, max-age=0, must-revalidate
Pragma: no-cache
Expires: 0
X-Frame-Options: DENY
X-RateLimit-Limit: 600
X-RateLimit-Remaining: 599
X-RateLimit-Reset: 60
Content-Type: application/hal+json;charset=UTF-8
Set-Cookie: JSESSIONID=6F0DD77B42E5CF11EFEFAD140BCD4F7A; Path=/inxmail3; HttpOnly

';
    }

    /**
     * Provide testdata for dryrun option
     */
    private function setTestResponseData()
    {
        self::$_responseCurrentBytes = 813;
        self::$_responseCode = 200;
        self::$_responseInfo = array(
            'url' => 'https://magento-dev.api.inxdev.de/magento-dev/rest/v1/',
            'content_type' => 'application/hal+json;charset=UTF-8',
            'http_code' => '200',
            'header_size' => '488',
            'request_size' => '313',
            'filetime' => '-1',
            'size_download' => '813',
            'download_content_length' => '-1',
            'upload_content_length' => '-1'
        );
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
        return (count($test) > 1 && in_array(strtolower($test[0]), array('http', 'https'))) ? true : false;
    }
}
