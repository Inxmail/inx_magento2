<?php
namespace Flagbit\Inxmail\Helper;

use \Flagbit\Inxmail\Model\Request;
use \Flagbit\Inxmail\Logger\Logger;
use \Flagbit\Inxmail\Helper\SubscriptionData;
use \Symfony\Component\Console\Output\OutputInterface;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Customer\Model\ResourceModel\CustomerRepository;
use \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use \Magento\Newsletter\Model\Subscriber;


class SubscriberSync extends AbstractHelper
{
    const ARG_TYPE_ALL = 'all';
    const ARG_TYPE_SUBSCRIBED = 'subscribed';
    const ARG_TYPE_UNSUBSCRIBED = 'unsubscribed';

    /** @var int */
    private $listId;
    /** @var \Magento\Framework\App\Helper\Context */
    private $context;
    /** @var \Flagbit\Inxmail\Logger\Logger */
    private $logger;
    /** @var \Flagbit\Inxmail\Model\Request */
    private $request;
    /** @var \Flagbit\Inxmail\Helper\SubscriptionData */
    private $subscriptionData;
    /** @var \Symfony\Component\Console\Output\OutputInterface */
    private $output;
    /** @var \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory */
    private $subcriberCollectionFactory;
    /** @var \Magento\Customer\Model\ResourceModel\CustomerRepository */
    private $customerRepsitory;
    /** @var boolean */
    private $compressed;

    public function __construct(
        Context $context,
        Logger $logger,
        Request $request,
        CollectionFactory $subcriberCollectionFactory,
        CustomerRepository $customerRepository,
        SubscriptionData $subscriptionData
    ){
        $this->context = $context;
        $this->logger = $logger;
        $this->request = $request;
        $this->subcriberCollectionFactory = $subcriberCollectionFactory;
        $this->customerRepsitory = $customerRepository;
        $this->subscriptionData = $subscriptionData;

        parent::__construct($context);
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutputInterface(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param int $listId
     */
    public function setListId(int $listId)
    {
        $this->listId = $listId;
    }

    public function sync(string $type, bool $compressed = true)
    {
        $this->compressed = $compressed;
        $baseData = array();

        switch ($type) {
            case self::ARG_TYPE_SUBSCRIBED:
                $baseData['subscribed'] = $this->getSubscriberData();
                break;
            case self::ARG_TYPE_UNSUBSCRIBED:
                $baseData['unsubscribed'] = $this->getUnsubscriberData();
                break;
            case self::ARG_TYPE_ALL:
            default:
                $baseData['subscribed'] = $this->getSubscriberData();
                $baseData['unsubscribed'] = $this->getUnsubscriberData();
        }

        $csvData = array();
        if (isset($baseData['subscribed'])) {
            $this->writeOutput('Prepare csv data for subscribed users');
            $csvData = $this->getCsvData($baseData['subscribed']);
        }

        if (!empty($csvData)) {
            $this->writeOutput('Sending subscribed data');
            $this->sendData($csvData);
        }

        if (isset($baseData['unsubscribed'])) {
            $this->writeOutput('Prepare csv data for other than subscribed users');
            $csvData = $this->getCsvData($baseData['unsubscribed']);
        }

        if (!empty($csvData)) {
            $this->writeOutput('Sending unsubscribed data');
            $this->sendData($csvData);
        }
    }

    private function getSubscriberData()
    {
        $this->writeOutput('Get subscriber data');
        $result = array();
        $subscriberColletion = $this->subcriberCollectionFactory->create()
            ->addFilter('subscriber_status', Subscriber::STATUS_SUBSCRIBED);

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        foreach ($subscriberColletion as $subscriber) {
            $result[] = $this->subscriptionData->getSubscriptionFields($subscriber);
            $result[count($result)-1]['email'] = $subscriber->getEmail();
        }

        $this->writeOutput('Fetched '.count($result).' subscriber');
        return $result;
    }

    private function getUnsubscriberData()
    {
        $this->writeOutput('Get unsubscribed data');

        $result = array();
        $subscriberColletion = $this->subcriberCollectionFactory->create()
            ->addFilter('subscriber_status', Subscriber::STATUS_UNSUBSCRIBED)
            ->addFilter('subscriber_status', Subscriber::STATUS_UNCONFIRMED)
            ->addFilter('subscriber_status', Subscriber::STATUS_NOT_ACTIVE);

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        foreach ($subscriberColletion as $subscriber) {
            $result[] = $this->subscriptionData->getSubscriptionFields($subscriber);
            $result[count($result)-1]['email'] = $subscriber->getEmail();
        }

        $this->writeOutput('Fetched '.count($result).' subscriber');
        return $result;
    }

    private function getCsvData(array $subscriberData): array
    {
        $result = array();

        $mapData = $this->subscriptionData->getMapping();
        ksort($mapData);
        $fields = array_keys($mapData);
        $result[] = $fields;

        // check fields/amount
        foreach ($fields as $key){
            foreach ($subscriberData as $subKey => $subscriber) {
                if (!array_key_exists($key, $subscriber)) {
                    $subscriberData[$subKey][$key] = '';
                }
            }
        }

        // order for equal output
        foreach ($subscriberData as $key => $value) {
            if (count($subscriberData) > 1 && count($fields) === count(array_keys($value))) {
                ksort($value);
                $result[] = $value;
            }
        }

        return $result;
    }

    private function sendData(array $subscribers)
    {

        /** @var \Flagbit\Inxmail\Model\Request\RequestImports client */
        $client = $this->request->getImportClient();

        $client->setRequestParam('?listId='.$this->listId);

        if ($this->compressed) {
            $client->setRequestFileGz($subscribers);
        } else {
            $client->setRequestFile($subscribers);
        }

        $response = $client->writeRequest();
        var_dump($response);
    }

    private function writeOutput(string $message)
    {
        if ($this->output !== null) {
            $message = date('[Y-m-d H:i:s] ', time()).$message;
            $this->output->writeln($message);
        }
    }
}
