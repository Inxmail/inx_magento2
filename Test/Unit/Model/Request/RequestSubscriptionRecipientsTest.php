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
namespace Flagbit\Inxmail\Test\Unit\Model\Request;

use \Flagbit\Inxmail\Model\Request;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestRecipientAttributesTest
 *
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
            $params = $_SERVER;
            $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
            /** @var \Magento\Framework\App\Http $app */
            $app = $bootstrap->createApplication('Magento\Framework\App\Http');
            unset($app);

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->om = $objectManager;

            $test = new Request(new \Flagbit\Inxmail\Model\Request\RequestFactory($this->om));
            $this->requestClient = $test->getSubscriptionsClient();
        }
    }

    public function testRequest(){
        $data = $this->requestClient->sendRequest();
        $this->assertArrayHasKey('_links', $data, 'not an array or no valid content');
        $this->assertEquals(200, $this->requestClient->getResponseCode(), 'wrong response code');
    }

}
