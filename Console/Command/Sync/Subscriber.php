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

namespace Flagbit\Inxmail\Console\Command\Sync;

use Flagbit\Inxmail\Console\Command\AbstractCommand;
use Flagbit\Inxmail\Helper\Config;
use Flagbit\Inxmail\Helper\SubscriberSync;
use Flagbit\Inxmail\Logger\Logger;
use Flagbit\Inxmail\Model\Config\SystemConfig;
use Magento\Framework\App\State;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Subscriber
 *
 * @package Flagbit\Inxmail\Console\Command\Sync
 */
class Subscriber extends AbstractCommand
{
    const COMMAND_NAME = 'inx:sync:subscriber';
    const ARGUMENT_TYPE = 'type';
    const OPTION_COMPRESSED = 'compressed';

    /** @var \Flagbit\Inxmail\Logger\Logger */
    private $logger;
    /** @var \Flagbit\Inxmail\Helper\SubscriberSync */
    private $subscriberSync;
    /** @var \Flagbit\Inxmail\Model\Config\SystemConfig */
    private $systemConfig;

    /**
     * Subscriber constructor.
     *
     * @param \Magento\Framework\App\State $state
     * @param \Flagbit\Inxmail\Helper\Config $config
     * @param \Flagbit\Inxmail\Helper\SubscriberSync $subscriberSync
     * @param \Flagbit\Inxmail\Logger\Logger $logger
     * @throws \LogicException
     */
    public function __construct(
        State $state,
        Config $config,
        SubscriberSync $subscriberSync,
        Logger $logger
    )
    {
        parent::__construct($state);

        $this->state = $state;
        $this->systemConfig = SystemConfig::getSystemConfig($config);
        $this->subscriberSync = $subscriberSync;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the name is invalid
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription(sprintf('Inxmail sync for subscribers'))
            ->addArgument(self::ARGUMENT_TYPE, InputArgument::OPTIONAL, '<comment>all, unsubscribed, subscribed</comment>')
            ->addOption(self::OPTION_COMPRESSED, '-c', InputOption::VALUE_OPTIONAL, '<comment>[0|1] send compressed data upload - default = 1</comment>');
    }

    /**
     * {@inheritdoc}
     */
    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        $args = $input->getArgument(self::ARGUMENT_TYPE);
        $compressed = $input->getOption('compressed');

        $type = $args[self::ARGUMENT_TYPE] ?? $this->subscriberSync::ARG_TYPE_SUBSCRIBED;

        try {
            $this->subscriberSync->setOutputInterface($output);
            $this->subscriberSync->setListId($this->systemConfig->getApiList());
            $this->subscriberSync->sync($type, $compressed ?? true);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
        }
    }
}
