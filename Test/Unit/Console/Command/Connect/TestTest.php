<?php

namespace Flagbit\Inxmail\Test\Unit\Console\Command\Connect;

use Symfony\Component\Console\Tester\CommandTester;
use Flagbit\Inxmail\Console\Command\Connect\Test;

/**
 * Class TestTest
 * @package Flagbit\Inxmail\Test\Unit\Console\Command\Connect
 * @runTestsInSeparateProcesses
 */
class TestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CheckActiveModulesCommand
     */
    private $command;

    public function setup(){

//        $state = $this->getMockClass('\Magento\Framework\App\State',[],[],'\Magento\Framework\App\State',false,true,true,false);
//        $helper = $this->getMockClass('Flagbit\Inxmail\Helper\Config');
        $state = $this->getMockBuilder('\Magento\Framework\App\State')
            ->disableOriginalConstructor()
            ->getMock();

        $helper = $this->getMockBuilder('Flagbit\Inxmail\Helper\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $helper->expects($this->at(0))
            ->method('getConfig')
            ->with('inxmail/general/api_url')
            ->will($this->returnValue('https://magento-dev.api.inxdev.de/magento-dev/rest/v1/'));
        $helper->expects($this->at(1))
            ->method('getConfig')
            ->will($this->returnValue('efb099d7-27ef-4241-92ab-33b3d0d3cd68'));
        $helper->expects($this->at(2))
            ->method('getConfig')
            ->will($this->returnValue('a84y_hRePQVe04mB3X8YnS0KjqhiRMb6hrRDLTk25AetXEFnT4soKRyUXAZOE398-TEetzkpmei4m7U5_Okbdg'));
        $helper->expects($this->at(3))
            ->method('getConfig')->withAnyParameters()
            ->will($this->returnValue('4'));

        $this->command = new Test($state, $helper);
    }

    public function testExecute(){
        $commandTester = new CommandTester($this->command);
//        $commandTester->getInput()->setInteractive(false);
        $commandTester->execute([]);

        $this->assertContains('https://magento-dev.api.inxdev.de/magento-dev/rest/v1/', $commandTester->getDisplay());
    }
}
