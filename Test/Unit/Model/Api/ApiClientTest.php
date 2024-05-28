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

namespace Flagbit\Inxmail\Test\Unit\Model\Api;

use Flagbit\Inxmail\Model\Api\ApiClient;
use \Flagbit\Inxmail\Exception\Api\MissingArgumentException;
use \Flagbit\Inxmail\Exception\Api\InvalidArgumentException;
use \Flagbit\Inxmail\Exception\Api\InvalidAuthenticationException;
use Flagbit\Inxmail\Model\Api\ApiClientFactory;

/**
 * Class ApiClientTest
 *
 * @package Flagbit\Inxmail\Test\Unit\Model\Api
 * @runTestsInSeparateProcesses
 */
class ApiClientTest extends \PHPUnit\Framework\TestCase
{

    /** @var  \Flagbit\Inxmail\Model\Api\ApiClient */
    protected $_apiClient;

    public function setUp()
    {
        $this->_apiClient = ApiClient::getApiClient();
    }


    public function testSetHeaderDefault()
    {
        $this->_apiClient->setRequestMethod(\Laminas\Http\Request::METHOD_POST);
        $this->_apiClient->setHeader();
        $header = $this->getObjectAttribute($this->_apiClient, '_defaultPostHeader');
        $this->assertAttributeEquals(
            $header,
            '_header',
            $this->_apiClient
        );
    }

    public function testSetMethodGet()
    {
        $this->_apiClient->setRequestMethod(\Laminas\Http\Request::METHOD_GET);
        $this->assertAttributeEquals(\Laminas\Http\Request::METHOD_GET,
            '_requestMethod',
            $this->_apiClient
        );
    }

    /**
     * @depends testSetMethodGet
     */
    public function testSetHeaderGet()
    {
        $this->_apiClient->setRequestMethod(\Laminas\Http\Request::METHOD_GET);
        $this->_apiClient->setHeader();
        $header = $this->getObjectAttribute($this->_apiClient, '_defaultHeader');
        $this->assertAttributeEquals(
            $header,
            '_header',
            $this->_apiClient
        );
    }

    public function testUrlException()
    {
        $this->expectException(MissingArgumentException::class);
        $this->_apiClient->getResource('', '', null, null, true);
    }

    public function testUrlExceptionMessage()
    {
        $this->expectExceptionMessage('URL Parameter missing');
        $this->_apiClient->getResource('', '', null, null, true);
    }

    public function testSetMethodException()
    {
        $method = \Zend_Http_Client::OPTIONS;
        $this->expectException(InvalidArgumentException::class);
        $this->_apiClient->setRequestMethod($method);
    }

    public function testSetMethodExceptionMessage()
    {
        $method = \Zend_Http_Client::OPTIONS;
        $this->expectExceptionMessage('Parameter for method not allowed');
        $this->_apiClient->setRequestMethod($method);
    }

    public function testSingleton()
    {
        $this->expectException(\Error::class);
        $this->test = new ApiClient();
    }

    public function testGetResourcesCredentialsExceptionMessage()
    {
        $this->expectExceptionMessage('Credentials not provided');
        $this->_apiClient->getResource('http://example.com', '', null, null);
    }

    public function testGetResourcesCredentialsException()
    {
        $this->expectException(MissingArgumentException::class);
        $this->_apiClient->getResource();
    }

    /**
     * @runInSeparateProcess
     */
    public function testAuthException()
    {
        $this->expectException(InvalidAuthenticationException::class);
        $this->_apiClient->getResource('http://example.com', '', null, null);
    }

    /**
     * @runInSeparateProcess
     */
    public function testAuthExceptionMessage()
    {
        $this->expectExceptionMessage('Credentials not provided');
        $this->_apiClient->getResource('http://example.com', '', null, null);
    }

    public function testSetCredentialsMethodSingle()
    {
        $cred = ['user:password'];
        $this->_apiClient->setCredentials($cred);
        $this->assertAttributeEquals(
            'user:password',
            '_credentials',
            $this->_apiClient);
    }

    public function testSetCredentialsMethodMultiple()
    {
        $cred = ['user' => 'username', 'password' => 'passwordhash'];
        $this->_apiClient->setCredentials($cred);
        $this->assertAttributeEquals(
            'username:passwordhash',
            '_credentials',
            $this->_apiClient);
    }

    public function testSetCredentialsException()
    {
        $cred = [1, 2, 3];
        $this->expectException(InvalidArgumentException::class);
        $this->_apiClient->setCredentials($cred);
    }

    public function testSetCredentialsExceptionMessage()
    {
        $cred = [1, 2, 3];
        $this->expectExceptionMessage('Parameters cannot be parsed');
        $this->_apiClient->setCredentials($cred);
    }

    public function testSetRequestUrl()
    {
        $url = 'http://test.com/';
        $this->_apiClient->setRequestUrl($url);
        $this->assertAttributeEquals(
            $url,
            '_requestUrl',
            $this->_apiClient);
    }

    public function testSetRequestUrlException()
    {
        $url = 'Htp://test.com';
        $this->expectException(InvalidArgumentException::class);
        $this->_apiClient->setRequestUrl($url);
    }

    public function testSetRequestUrlTypeException()
    {
        $url = null;
        $this->expectException(\TypeError::class);
        $this->_apiClient->setRequestUrl($url);
    }

    public function testRequest()
    {
        $this->_apiClient->setRequestUrl('http://example.com');
        $this->_apiClient->setRequestMethod(\Laminas\Http\Request::METHOD_GET);
        $response = $this->_apiClient->getResource('', '', null, ['test', 'test']);
        $this->assertNotEmpty($response);
    }

    public function testRequestresponseCode()
    {
        $this->_apiClient->setRequestUrl('http://example.com');
        $this->_apiClient->setRequestMethod(\Laminas\Http\Request::METHOD_GET);
        $this->_apiClient->getResource('', '', null, ['test', 'test']);
        $this->assertEquals(200, $this->_apiClient->getResponseStatusCode(), 'Wrong return, request failed');
    }
}
