<?php
namespace Flagbit\Inxmail\Console\Command\Sync;

use Flagbit\Inxmail\Console\Command\AbstractCommand;
use Flagbit\Inxmail\Model\Api\ApiClient;
use Flagbit\Inxmail\Helper\Config;
use Flagbit\Inxmail\Model\Config\SystemConfig;
use Magento\Framework\App\State;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputOption;

class Subscriber extends AbstractCommand
{
    const COMMAND_NAME = 'inx:sync:subscriber';

    const ARGUMENT_TYPE = 'type';

    const ARG_TYPE_ALL = 'all';
    const ARG_TYPE_SUBSCRIBED = 'subscribed';
    const ARG_TYPE_UNSUBSCRIBED = 'unsubscribed';

    public function __construct(
        State $state,
        Config $config
    )
    {
        parent::__construct($state);

        $this->state = $state;
        $this->config = $config;
        $this->systemConfig = SystemConfig::getSystemConfig($this->config);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription(sprintf('Inxmail sync for subscribers'))
            ->addArgument(self::ARGUMENT_TYPE, InputArgument::REQUIRED, '<comment>all, unsubscribed, subscribed</comment>');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        $args = $input->getArguments();
        $type = 'all';
        if (count($args) > 0) {
            $type = isset($args[self::ARGUMENT_TYPE]) ? $args[self::ARGUMENT_TYPE] : 'all';
        }


    }
}
