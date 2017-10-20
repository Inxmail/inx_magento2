<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @copyright Copyright (c) 2017 Flagbit GmbH
 */
namespace Flagbit\Inxmail\Test\Unit\Model\Request;

use Flagbit\Inxmail\Model\Request\RequestLists;
use Flagbit\Inxmail\Model\Request\RequestRecipientAttributes;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestRecipientAttributesTest
 *
 * @package Flagbit\Inxmail\Test\Unit\Model\Request
 * @runInSeparateProcess
 */
class RequestListsTest extends \PHPUnit\Framework\TestCase
{

    /** @var  RequestRecipientAttributes */
    private $requestClient;
    private $requestResponse;
    protected static $testListId;

    public function setUp()
    {

        if (!$this->requestClient) {
            $params = $_SERVER;
            $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
            /** @var \Magento\Framework\App\Http $app */
            $app = $bootstrap->createApplication('Magento\Framework\App\Http');
            unset($app);

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->om = $objectManager;


            $this->requestClient = (new \Flagbit\Inxmail\Model\Request\RequestFactory($this->om))->create(RequestLists::class, array());
        }
    }

    public function testSendRequestResponse()
    {
        $this->requestResponse = $this->requestClient->sendRequest();
        $this->assertArrayHasKey('_links', $this->requestResponse, 'Not an Array');
    }

    public function testSendRequestJsonResponse()
    {
        $this->requestResponse = $this->requestClient->sendRequest();
        $this->assertJson($this->requestClient->getResponseJson(), 'Not a Json');
    }

    public function testSendRequestArrayResponse()
    {
        $this->requestResponse = $this->requestClient->sendRequest();
        $arrResponse = $this->requestClient->getResponseArray();
        $this->assertTrue(is_array($arrResponse), 'Not an array');
        $this->assertArrayHasKey('_links', $arrResponse, 'Wrong content');
    }

    public function testMultipleRequests()
    {
        for ($ii = 0; $ii < 3; $ii++) {
            $arrResponse = $this->requestClient->sendRequest();
            $this->assertTrue(is_array($arrResponse), 'Not an array');
            $this->assertArrayHasKey('_links', $arrResponse, 'Wrong content');
        }
    }

    public function testSetRequestParam()
    {
        $this->requestClient->setRequestParam('1');
        $this->assertEquals('1', $this->requestClient->getRequestParam());
    }

    public function testSendRequestWithParam()
    {
        $this->requestClient->setRequestParam('4');
        $this->requestResponse = $this->requestClient->sendRequest();

        $this->assertTrue(is_array($this->requestResponse), 'Not an array');
        $this->assertArrayHasKey('name', $this->requestResponse);
    }

    public function testCreateList()
    {
        $name = "test-x";
        $this->requestClient->setRequestData(json_encode(array(
                'name' => $name,
                'type' => RequestLists::LIST_TYPE_STANDARD,
                'senderAddress' => 'testing@example.com',
                'senderName' => 'Sender Doe',
                'replyToAddress' => 'testing@example.com',
                'replyToName' => 'Return Doe',
                'description' => 'testing create list'
            ))
        );

        $result = $this->requestClient->writeRequest();
        $this->assertEquals(201, $result, "list was not created");
        $this->assertEquals($name, $this->requestClient->getResponseArray()['name'], "list name wrong");
        self::$testListId =  $this->requestClient->getResponseArray()['id'];
    }

    public function testPutRequest()
    {
        $this->requestClient->setRequestParam(self::$testListId);
        $this->requestResponse = $this->requestClient->sendRequest();

        $test = array();
        $comp = array('name', 'senderAddress', 'senderName', 'replyToAddress', 'replyToName', 'description');
        foreach ($this->requestResponse as $key => $value) {
            if (in_array($key, $comp)) {
                $test[$key] = $value;
            }
        }

        $test['description'] = 'test a new ' . rand(1, 2000);
        $this->requestClient->setRequestData(json_encode($test));
        $result = $this->requestClient->putRequest(self::$testListId);
        $this->assertEquals($test['description'], $this->requestClient->getResponseArray()['description']);
        $this->assertEquals(200, $result, "request failed");
    }

    public function testDeleteRequest()
    {
        $this->requestResponse = $this->requestClient->deleteRequest(self::$testListId);
        $this->assertEquals(204, $this->requestResponse, "request failed");
    }
}
