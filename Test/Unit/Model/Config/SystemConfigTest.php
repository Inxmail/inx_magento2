<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @author Flagbit GmbH
 * @copyright Copyright © 2017-2025 Inxmail GmbH
 * @license Licensed under the Open Software License version 3.0 (https://opensource.org/licenses/OSL-3.0)
 *
 */

namespace Flagbit\Inxmail\Test\Unit\Model\Config;
use Flagbit\Inxmail\Model\Config\SystemConfig;

/**
 * Class SystemConfigTest
 *
 * @package Flagbit\Inxmail\Test\Unit\Model\Config
 * @runTestsInSeparateProcesses
 */
class SystemConfigTest extends \PHPUnit\Framework\TestCase
{
    private $configHelper;
    private $configModel;

    public function setUp(): void
    {
        $this->configHelper = $this->getMockBuilder(\Flagbit\Inxmail\Helper\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configModel = SystemConfig::getSystemConfig($this->configHelper);
    }

    public function testGetConfigUrl()
    {
        $this->configHelper->expects($this->once())
            ->method('getConfig')
            ->with('inxmail/general/api_url')
            ->willReturn('http://tes.example.com/testing');

        $modelReturn = $this->configModel->getApiUrl();
        $this->assertEquals('http://tes.example.com/testing', $modelReturn);
    }
}
