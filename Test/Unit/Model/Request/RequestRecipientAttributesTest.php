<?php
/**
 * Created by PhpStorm.
 * User: peter_lelewel
 * Date: 21.09.17
 * Time: 12:59
 */

namespace Flagbit\Inxmail\Test\Unit\Model\Request;

use Flagbit\Inxmail\Model\Request\RequestRecipientAttributes;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestRecipientAttributesTest
 * @package Flagbit\Inxmail\Test\Unit\Model\Request
 * @runInSeparateProcess
 */
class RequestRecipientAttributesTest extends \PHPUnit\Framework\TestCase
{

    /** @var  RequestRecipientAttributes */
    private $requestClient;
    private $requestResponse;

    public function setUp()
    {

        if (!$this->requestClient) {
//            var_dump($this->requestClient);
        $params = $_SERVER;
        $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
        /** @var \Magento\Framework\App\Http $app */
        $app = $bootstrap->createApplication('Magento\Framework\App\Http');
        unset($app);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->om = $objectManager;


        $this->requestClient = (new \Flagbit\Inxmail\Model\Request\RequestFactory($this->om))->create(RequestRecipientAttributes::class, array());
        }
    }

    public function testSendRequestResponse(){
        $this->requestResponse = $this->requestClient->sendRequest();
        $this->assertArrayHasKey('_links', $this->requestResponse, 'Not an Array');
    }

    public function testSendRequestJsonResponse(){
        $this->requestResponse = $this->requestClient->sendRequest();
        $this->assertJson($this->requestClient->getResponseJson(), 'Not a Json');
    }

    public function testSendRequestArrayResponse(){
        $this->requestResponse = $this->requestClient->sendRequest();
        $arrResponse = $this->requestClient->getResponseArray();
        $this->assertTrue(is_array($arrResponse), 'Not an array');
        $this->assertArrayHasKey('_links', $arrResponse, 'Wrong content');
    }

    public function testMultipleRequests(){
        for ($ii = 0; $ii < 3; $ii++) {
            $arrResponse = $this->requestClient->sendRequest();
            $this->assertTrue(is_array($arrResponse), 'Not an array');
            $this->assertArrayHasKey('_links', $arrResponse, 'Wrong content');
        }
    }

    public function testSetRequestParam(){
        $this->requestClient->setRequestParam('1');
        $this->assertEquals('1', $this->requestClient->getRequestParam());
    }

    public function testSendRequestWithParam(){
        $this->requestClient->setRequestParam('7');
        $this->requestResponse = $this->requestClient->sendRequest();

        $this->assertTrue(is_array($this->requestResponse), 'Not an array');
        $this->assertArrayHasKey('name', $this->requestResponse);
    }

    public function testCreateRecipientAttribute(){
        // {"name":"testattrib","id":19,"type":"INTEGER","_links":{"self":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/attributes/19"}}}
    }
}
