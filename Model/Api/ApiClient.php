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
use Zend_Uri;

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
     * Request allowed header accept types
     *
     * @var array
     */
    protected static $_allowedAcceptTypes = array(
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
    protected static $_allowedPostTypes = array(
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
     *
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

            if ($this->_requestMethod !== \Zend_Http_Client::GET && $this->_requestMethod !== \Zend_Http_Client::DELETE) {
                $this->setRequestMethod(\Zend_Http_Client::GET);
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

            $response = $this->_requestClient->read();
            $this->_responseBody = \Zend_Http_Response::extractBody($response);
            $this->_responseHeader = $this->extractHeaders($response);

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

            if ($this->_requestMethod !== \Zend_Http_Client::POST && $this->_requestMethod !== \Zend_Http_Client::PUT) {
                $this->setRequestMethod(\Zend_Http_Client::POST);
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

            $response = $this->_requestClient->read();

            $this->_responseBody = \Zend_Http_Response::extractBody($response);
            $this->_responseHeader = $this->extractHeaders($response);

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
        $this->setRequestMethod(\Zend_Http_Client::PUT);
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
        $this->setRequestMethod(\Zend_Http_Client::DELETE);
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
        return (count($test) > 1 && in_array(strtolower($test[0]), array('http', 'https'), true));
    }

    /**
     * Replaces \Zend_Http_Response::extractHeaders because of HTTP/2 incompatibility
     *
     * @param string $response
     *
     * @return array
     */
    private function extractHeaders(string $response): array
    {
        $headers = array();

        // First, split body and headers. Headers are separated from the
        // message at exactly the sequence "\r\n\r\n"
        $parts = preg_split('|(?:\r\n){2}|m', $response, 2);
        if (! $parts[0]) {
            return $headers;
        }

        // Split headers part to lines; "\r\n" is the only valid line separator.
        $lines = explode("\r\n", $parts[0]);
        unset($parts);
        $last_header = null;

        foreach($lines as $index => $line) {
            if ($index === 0 && preg_match('#^HTTP/\d+(?:\.\d+)? [1-5]\d+#', $line)) {
                // Status line; ignore
                continue;
            }

            if ($line == "") {
                // Done processing headers
                break;
            }

            // Locate headers like 'Location: ...' and 'Location:...' (note the missing space)
            if (preg_match("|^([a-zA-Z0-9\'`#$%&*+.^_\|\~!-]+):\s*(.*)|s", $line, $m)) {
                unset($last_header);
                $h_name  = strtolower($m[1]);
                $h_value = $m[2];
                \Zend_Http_Header_HeaderValue::assertValid($h_value);

                if (isset($headers[$h_name])) {
                    if (! is_array($headers[$h_name])) {
                        $headers[$h_name] = array($headers[$h_name]);
                    }

                    $headers[$h_name][] = ltrim($h_value);
                    $last_header = $h_name;
                    continue;
                }

                $headers[$h_name] = ltrim($h_value);
                $last_header = $h_name;
                continue;
            }

            // Identify header continuations
            if (preg_match("|^[ \t](.+)$|s", $line, $m) && $last_header !== null) {
                $h_value = trim($m[1]);
                if (is_array($headers[$last_header])) {
                    end($headers[$last_header]);
                    $last_header_key = key($headers[$last_header]);

                    $h_value = $headers[$last_header][$last_header_key] . $h_value;
                    \Zend_Http_Header_HeaderValue::assertValid($h_value);

                    $headers[$last_header][$last_header_key] = $h_value;
                    continue;
                }

                $h_value = $headers[$last_header] . $h_value;
                \Zend_Http_Header_HeaderValue::assertValid($h_value);

                $headers[$last_header] = $h_value;
                continue;
            }

            // Anything else is an error condition
            #require_once 'Zend/Http/Exception.php';
            throw new \Zend_Http_Exception('Invalid header line detected');
        }

        return $headers;
    }
}
