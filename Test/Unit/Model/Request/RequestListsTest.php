<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @copyright Copyright (c) 2017 Flagbit GmbH
 */

/**
 * Created by PhpStorm.
 * User: peter_lelewel
 * Date: 21.09.17
 * Time: 12:59
 */

namespace Flagbit\Inxmail\Test\Unit\Model\Request;

use Flagbit\Inxmail\Model\Request\RequestLists;
use Flagbit\Inxmail\Model\Request\RequestRecipientAttributes;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestRecipientAttributesTest
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
//            var_dump($this->requestClient);
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
        $name = "test";
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

//        $result = $this->requestClient->writeRequest();
//        $this->assertEquals(201, $result, "list was not created");
//        $this->assertEquals($name, $this->requestClient->getResponseArray()['name'], "list name wrong");
//        self::$testListId =  $this->requestClient->getResponseArray()['id'];
//        var_dump(self::$testListId);
        // {"id":5,"creationDate":"2017-09-25T08:38:33Z","name":"test","description":"testing create list","type":"STANDARD","senderAddress":"testing@example.com","senderName":"Sender Doe","replyToAddress":"testing@example.com","replyToName":"Return Doe","_links":{"self":{"href":"https://magento-dev.api.inxdev.de/magento-dev/rest/v1/lists/5"}}}
    }

    public function testPutRequest()
    {
        $this->requestClient->setRequestParam(self::$testListId);
        $this->requestResponse = $this->requestClient->sendRequest();

        $test = array();
        $comp = array('name', 'senderAddress', 'senderName', 'replyToAddress', 'replyToName', 'description');
        var_dump($this->requestResponse);
        foreach ($this->requestResponse as $key => $value) {
            if (in_array($key, $comp)) {
                $test[$key] = $value;
            }
        }

        $test['description'] = 'test a new ' . rand(1, 2000);
        $this->requestClient->setRequestData(json_encode($test));
//        $result = $this->requestClient->putRequest(self::$testListId);
//        $this->assertEquals($test['description'], $this->requestClient->getResponseArray()['description']);
//        $this->assertEquals(200, $result, "request failed");

       /* array(10) {
        'id' =>
  int(5)
  'creationDate' =>
  string(20) "2017-09-25T08:38:33Z"
  'name' =>
  string(4) "test"
  'description' =>
  string(15) "test a new 1480"
  'type' =>
  string(8) "STANDARD"
  'senderAddress' =>
  string(19) "testing@example.com"
  'senderName' =>
  string(10) "Sender Doe"
  'replyToAddress' =>
  string(19) "testing@example.com"
  'replyToName' =>
  string(10) "Return Doe"
  '_links' =>
  array(1) {
            'self' =>
    array(1) {
                'href' =>
      string(61) "https://magento-dev.api.inxdev.de/magento-dev/rest/v1/lists/5"
    }
  }
}*/
    }

    public function testDeleteRequest()
    {
//        $this->requestResponse = $this->requestClient->deleteRequest(self::$testListId);
//        $this->assertEquals(204, $this->requestResponse, "request failed");
    }
}
