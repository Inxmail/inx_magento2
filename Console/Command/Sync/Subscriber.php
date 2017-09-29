<?php
namespace Flagbit\Inxmail\Console\Command\Sync;

use \Flagbit\Inxmail\Console\Command\AbstractCommand;
use \Flagbit\Inxmail\Helper\Config;
use \Flagbit\Inxmail\Helper\SubscriberSync;
use \Flagbit\Inxmail\Model\Config\SystemConfig;
use \Flagbit\Inxmail\Logger\Logger;
use \Magento\Framework\App\State;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Subscriber
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
     * @param State $state
     * @param Config $config
     * @param SubscriberSync $subscriberSync
     * @param Logger $logger
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
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription(sprintf('Inxmail sync for subscribers'))
            ->addArgument(self::ARGUMENT_TYPE, InputArgument::OPTIONAL, '<comment>all, unsubscribed, subscribed</comment>')
            ->addOption(self::OPTION_COMPRESSED, '-c',InputOption::VALUE_OPTIONAL,'<comment>[0|1] send compressed data upload - default = 1</comment>');
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        $args = $input->getArgument(self::ARGUMENT_TYPE);
        $compressed = $input->getOption('compressed');

        $type = 'all';
        if (count($args) > 0) {
            $type = isset($args[self::ARGUMENT_TYPE]) ?? $this->subscriberSync::ARG_TYPE_SUBSCRIBED;
        }

        try {
            $this->subscriberSync->setOutputInterface($output);
            $this->subscriberSync->setListId($this->systemConfig->getApiList());
            $this->subscriberSync->sync($type, ($compressed ?? true));
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
        }
    }
}
