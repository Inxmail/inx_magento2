<?php
namespace Flagbit\Inxmail\Console\Command\Connect;

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

class Test extends AbstractCommand
{
    const COMMAND_NAME = 'inx:connect:test';

    /** @var \Flagbit\Inxmail\Model\Api\ApiClient */
    private $apiClient;
    /** @var \Flagbit\Inxmail\Model\Config\Backend\SystemConfig */
    private $systemConfig;

    /** @var \Magento\Framework\Logger\Monolog  */
    private $logger;

    const OPTION_MODIFY_INPUT_FILE  = 'modify-input-file';
    const OPTION_MODIFY_OUTPUT_FILE = 'modify-output-file';
    const OPTION_MODIFY_ROOT_NAME   = 'modify-root-name';
    const OPTION_IGNORE_WORKER      = 'ignore-worker';
    const OPTION_DRY_RUN            = 'dry-run';

    public function __construct(
        State $state,
        Config $config
//        LoggerInterface $logger
    )
    {
        parent::__construct($state);
        $this->apiClient = ApiClient::getApiClient();
        $this->state = $state;
        $this->config = $config;
        $this->systemConfig = SystemConfig::getSystemConfig($this->config);
//        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription(sprintf('<error>Testing console against url</error>'))
            ->addArgument('override-base', InputArgument::OPTIONAL, '<error>override base url</error>')
            ->addOption('add-url', '-u',InputOption::VALUE_OPTIONAL,'<comment>additional url parameter to add after base</comment>')
            ->addOption('dry-run', '-d',InputOption::VALUE_OPTIONAL,'<comment>no real server invoked</comment>');


//        parent::configure();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOptions();

        $dryRun = true;
        if (isset($options['dry-run'])){
            $dryRun = (bool)$options['dry-run'];
        }

        $url = $this->getUrl($input, $output);

        $output->writeln($url);
        $cred = array($this->systemConfig->getApiUser(), $this->systemConfig->getApiKey());
        $list = $this->systemConfig->getApiList();


        $result = $this->apiClient->getResource($url, '',null, $cred, $dryRun);
        $res = json_decode($result, true);

//        $attrName = 'inx:attributes';
//        var_dump($res->_links->$attrName);
//        foreach ($res->_links as $key => $val){
//            var_dump($key, $res->_links->$key);
//        }

//        foreach ($result as $key => $value) {
//            var_dump($key, $value);
//        }
//        var_dump($result, json_decode($result), json_decode($result, true));
    }

    private function getUrl($input, $output)
    {
        $arguments = $input->getArguments();
        $options = $input->getOptions();
        $url = '';
        if (!$arguments['override-base'])
            $url = $this->systemConfig->getApiUrl();
        else
            $url= $arguments['override-base'];

        $url = explode(':',$url);

        if (count($url) > 1 && (strtolower($url[0]) === 'http' || strtolower($url[0]) === 'https')) {
            $url = implode(':', $url);
        } else { return ''; }


        $url .= (substr($url, strlen($url)-1) === '/') ? '' : '/';

        $addUrl = '';
        if (!empty($options['add-url'])){
            $addUrl = $options['add-url'];
            $addUrl = (substr($addUrl, 0,1) === '/') ? substr($addUrl, 1) : $addUrl;
            $addUrl .= (substr($addUrl, strlen($addUrl)-1) === '/') ? '' : '/';
        } /*else if ($input->isInteractive()){
            $addUrl = $this->getUrlInput($input, $output);
        }*/
        return $url.$addUrl;
    }

    private function getUrlInput($input, $output)
    {
        $helper = $this->getHelper('question');
        $question = new \Symfony\Component\Console\Question\ChoiceQuestion(
            'Select test url (default: blank)',
            array(' ', 'attributes', 'recipients', 'lists','lists/4', 'imports/recipients', 'events/subscriptions', 'events/unsubscriptions', 'bounces'),
            0
        );
        $question->setErrorMessage('Invalid choice');
        return $helper->ask($input, $output, $question);
    }

    //#########Testing functions #############//
    private function logTest(){
        $this->logger->log($this->logger::DEBUG, 'debug logging');
        $this->logger->log($this->logger::INFO, 'info logging');
        $this->logger->log($this->logger::NOTICE, 'notice logging');
        $this->logger->log($this->logger::WARNING, 'warning logging');
        $this->logger->log($this->logger::ERROR, 'error logging');
        $this->logger->log($this->logger::CRITICAL, 'critical logging');
        $this->logger->log($this->logger::ALERT, 'alert logging');
        $this->logger->log($this->logger::EMERGENCY, 'emergency logging');
    }

    private function askConfirm($input, $output) {
        $helper = $this->getHelper('question');
        $question = new \Symfony\Component\Console\Question\ConfirmationQuestion(
            'Continue with this action?',
            false,
            '/^(y|j)/i'
        );
        return $helper->ask($input, $output, $question);
    }

    private function askChoice($input, $output) {
        $helper = $this->getHelper('question');
        $question = new \Symfony\Component\Console\Question\ChoiceQuestion(
            'Please select your favorite color (defaults to red)',
            array('red', 'blue', 'yellow'),
            0
        );
        $question->setErrorMessage('Color %s is invalid.');
        return $helper->ask($input, $output, $question);
    }

    private function askInputWithDefault($input, $output)
    {
        $helper = $this->getHelper('question');
        $question = new \Symfony\Component\Console\Question\Question('Please enter the name of the bundle', 'AcmeDemoBundle');
        return $helper->ask($input, $output, $question);
    }

    private function askInputWithAutocomplete($input, $output)
    {
        $helper = $this->getHelper('question');
        $bundles = array('AcmeDemoBundle', 'AcmeBlogBundle', 'AcmeStoreBundle');
        $question = new \Symfony\Component\Console\Question\Question('Please enter the name of a bundle', 'FooBundle');
        $question->setAutocompleterValues($bundles);
        return $helper->ask($input, $output, $question);
    }

    private function askInputHidden($input, $output)
    {
        $helper = $this->getHelper('question');
        $question = new \Symfony\Component\Console\Question\Question('What is the database password?');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        return $helper->ask($input, $output, $question);
    }


    private function outputFormatting(\Symfony\Component\Console\Output\OutputInterface $output){
        // green
        $output->writeln('<info>foo</info>');
        // brown
        $output->writeln('<comment>foo</comment>');
        // black on blue
        $output->writeln('<question>foo</question>');
        // white on red
        $output->writeln('<error>foo</error>');

        $style = new \Symfony\Component\Console\Formatter\OutputFormatterStyle('red', 'yellow', array('bold', 'blink'));
        $output->getFormatter()->setStyle('fire', $style);
        $output->writeln('<fire>foo</fire>');
    }
}
