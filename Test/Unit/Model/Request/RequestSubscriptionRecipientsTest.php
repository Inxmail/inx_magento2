<?php
/**
 * Created by PhpStorm.
 * User: peter_lelewel
 * Date: 21.09.17
 * Time: 12:59
 */

namespace Flagbit\Inxmail\Test\Unit\Model\Request;

use Flagbit\Inxmail\Model\Request\RequestSubscriptionRecipients;
use \Flagbit\Inxmail\Model\Request;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestRecipientAttributesTest
 * @package Flagbit\Inxmail\Test\Unit\Model\Request
 * @runInSeparateProcess
 */
class RequestSubscriptionRecipientsTest extends TestCase
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

        $test = new Request(new \Flagbit\Inxmail\Model\Request\RequestFactory($this->om));
        $this->requestClient = $test->getSubscriptionsClient();
//        $this->requestClient = (new \Flagbit\Inxmail\Model\Request\RequestFactory($this->om))->create(RequestSubscriptionRecipients::class, array());
        }
    }

    public function testRequest(){
        $data = $this->requestClient->sendRequest();
        var_dump($data);
        $this->assertArrayHasKey('_links', $data);
    }

}
