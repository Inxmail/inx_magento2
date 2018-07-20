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

namespace Flagbit\Inxmail\Helper;

use \Flagbit\Inxmail\Model\Request;
use \Flagbit\Inxmail\Logger\Logger;
use \Symfony\Component\Console\Output\OutputInterface;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use \Magento\Newsletter\Model\Subscriber;
use \Magento\Framework\ObjectManagerInterface;

/**
 * Class SubscriberSync
 *
 * @package Flagbit\Inxmail\Helper
 */
class SubscriberSync extends AbstractHelper
{
    const ARG_TYPE_ALL = 'all';
    const ARG_TYPE_SUBSCRIBED = 'subscribed';
    const ARG_TYPE_UNSUBSCRIBED = 'unsubscribed';

    /** @var int */
    private $listId;
    /** @var \Flagbit\Inxmail\Logger\Logger */
    private $logger;
    /** @var \Flagbit\Inxmail\Model\Request */
    private $request;
    /** @var \Flagbit\Inxmail\Helper\SubscriptionData */
    private $subscriptionData;
    /** @var \Symfony\Component\Console\Output\OutputInterface */
    private $output;
    /** @var \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory */
    private $subscriberCollectionFactory;
    /** @var boolean */
    private $compressed;
    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /**
     * SubscriberSync constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Flagbit\Inxmail\Logger\Logger $logger
     * @param \Flagbit\Inxmail\Model\Request $request
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollectionFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        Context $context,
        Logger $logger,
        Request $request,
        CollectionFactory $subscriberCollectionFactory,
        ObjectManagerInterface $objectManager
    )
    {
        $this->logger = $logger;
        $this->request = $request;
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->objectManager = $objectManager;

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

    /**
     * @param string $type
     * @param bool $compressed
     *
     * @throws \InvalidArgumentException When unknown output type is given
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sync(string $type, bool $compressed = true)
    {
        /** @var \Flagbit\Inxmail\Helper\SubscriptionData subscriptionData */
        $this->subscriptionData = $this->objectManager->create(SubscriptionData::class);

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
            $csvDataUnsub = $this->getCsvData($baseData['unsubscribed']);
        }

        if (!empty($csvDataUnsub)) {
            $this->writeOutput('Sending unsubscribed data');
            $this->sendData($csvDataUnsub);
        }
    }

    /**
     * @return array
     *
     * @throws \InvalidArgumentException When unknown output type is given
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSubscriberData(): array
    {
        $this->writeOutput('Get subscriber data');
        $result = array();
        /** @var \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection $subscriberCollection */
        $subscriberCollection = $this->subscriberCollectionFactory->create()
            ->addFilter('subscriber_status', Subscriber::STATUS_SUBSCRIBED);

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        foreach ($subscriberCollection as $subscriber) {
            $result[] = $this->subscriptionData->getSubscriptionFields($subscriber);
            $result[count($result) - 1]['email'] = $subscriber->getEmail();
        }

        $this->writeOutput('Fetched ' . count($result) . ' subscriber');
        return $result;
    }

    /**
     * @return array
     *
     * @throws \InvalidArgumentException When unknown output type is given
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getUnsubscriberData(): array
    {
        $this->writeOutput('Get unsubscribed data');

        $result = array();
        /** @var \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection $subscriberCollection */
        $subscriberCollection = $this->subscriberCollectionFactory->create()
            ->addFilter('subscriber_status', Subscriber::STATUS_UNSUBSCRIBED)
            ->addFilter('subscriber_status', Subscriber::STATUS_UNCONFIRMED)
            ->addFilter('subscriber_status', Subscriber::STATUS_NOT_ACTIVE);

        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        foreach ($subscriberCollection as $subscriber) {
            $result[] = $this->subscriptionData->getSubscriptionFields($subscriber);
            $result[count($result) - 1]['email'] = $subscriber->getEmail();
        }

        $this->writeOutput('Fetched ' . count($result) . ' subscriber');
        return $result;
    }

    /**
     * @param array $subscriberData
     *
     * @return array
     */
    private function getCsvData(array $subscriberData): array
    {
        $result = array();

        $mapData = $this->subscriptionData->getMapping();
        $mapData['email'] = 'email';
        ksort($mapData);
        $fields = array_keys($mapData);
        $result[] = $fields;

        // check fields/amount
        foreach ($fields as $key) {
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

    /**
     * @param array $subscribers
     *
     * @return int
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    private function sendData(array $subscribers): int
    {

        /** @var \Flagbit\Inxmail\Model\Request\RequestImports client */
        $client = $this->request->getImportClient();

        $client->setRequestParam('?listId=' . $this->listId);

        if ($this->compressed) {
            $client->setRequestFileGz($subscribers);
        } else {
            $client->setRequestFile($subscribers);
        }

        $response = 0;
        try {
            $response = $client->writeRequest();
        } catch (\Exception $e) {
            $this->writeOutput('<error>Exception: Something went wrong, see log for information</error>');
            $this->logger->alert('Inxmail Api Error' . PHP_EOL, array($e->getFile(), $e->getLine(), $e->getMessage(), $e->getTrace()));
        }

        if ($response !== 201) {
            $this->writeOutput('<question>Info: Something went wrong, see log for information</question>');
            $this->logger->alert('Inxmail Api Error' . PHP_EOL, array($client->getResponseHeader(), $client->getResponseArray()));
        }

        return $response > 0 ?? 0;
    }

    /**
     * @return bool
     */
    public function isCompressed(): bool
    {
        return $this->compressed;
    }

    /**
     * @param bool $compressed
     */
    public function setCompressed(bool $compressed)
    {
        $this->compressed = $compressed;
    }

    /**
     * @param string $message
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    private function writeOutput(string $message)
    {
        if ($this->output !== null) {
            $message = date('[Y-m-d H:i:s] ') . $message;
            $this->output->writeln($message);
        }
    }
}
