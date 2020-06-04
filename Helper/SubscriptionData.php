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

use DateTimeZone;
use Exception;
use Flagbit\Inxmail\Logger\Logger;
use Flagbit\Inxmail\Model\Config\SystemConfig;
use Flagbit\Inxmail\Model\Request\RequestRecipientAttributes;
use Flagbit\Inxmail\Model\Request\RequestSubscriptionRecipients;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\Subscriber;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use function in_array;
use function is_array;
use function json_decode;

/**
 * Class Config
 *
 * @package Flagbit\Inxmail\Helper
 */
class SubscriptionData extends AbstractHelper
{

    private const FORMAT_DATE_TIME = 'Y-m-d\TH:i:s\Z';
    private const FORMAT_DATE_ONLY = 'Y-m-d';

    /**
     * @var Session
     */
    protected $_customerSession;
    /**
     * @var CustomerRepository
     */
    protected $_customerRepository;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var SystemConfig
     */
    protected $_sysConfig;
    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Config constructor.
     *
     * @param Context $context
     * @param CustomerRepository $customerRepository
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param CollectionFactory $orderCollectionFactory
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        CustomerRepository $customerRepository,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        CollectionFactory $orderCollectionFactory,
        Config $config,
        Logger $logger
    ) {
        $this->_customerRepository = $customerRepository;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_sysConfig = SystemConfig::getSystemConfig($config);
        $this->logger = $logger;

        parent::__construct($context);
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @param Subscriber $subscriber
     *
     * @return array
     *
     * @throws LocalizedException
     */
    public function getSubscriptionFields(Subscriber $subscriber): array
    {
        $data = $this->getSubscriptionStaticData($subscriber);
        $data = $this->cleanData($data);

        $map = $this->getMapping();

        $result = [];
        foreach ($map as $inxKey => $magKey) {
            if ($inxKey === 'email') {
                continue;
            }
            $keys = array_keys($data);
            if (isset($data[$magKey]) && in_array($magKey, $keys, true)) {
                $result[$inxKey] = $data[$magKey];
            }
        }

        return $result;
    }

    /**
     * @param Subscriber $subscriber
     *
     * @return array
     *
     * @throws LocalizedException
     */
    private function getSubscriptionStaticData(Subscriber $subscriber): array
    {

        $data = [];
        $data['subscriberId'] = $subscriber->getId();
        $data['status'] = $subscriber->getSubscriberStatus();
        $data['subscriberToken'] = $subscriber->getSubscriberConfirmCode();

        $customerId = $subscriber->getCustomerId();
        $customerData = $this->getCustomerData($customerId);

        $data['storeId'] = $subscriber->getStoreId();
        $storeData = $this->getStoreData($data['storeId']);
        return array_merge($data, $storeData, $customerData);
    }

    /**
     * @param int $customerId
     *
     * @return array
     *
     */
    private function getCustomerData(int $customerId): array
    {
        $data = [];
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->getCustomerById($customerId);

        if (null !== $customer) {
            $data['firstName'] = $customer->getFirstname();
            $data['lastName'] = $customer->getLastname();

            $data['gender'] = $customer->getGender();
            $data['group'] = $customer->getGroupId();
            $data['prefix'] = $customer->getPrefix();

            $lastOrderDate = $this->getLastOrderDate($customer->getId());
            if (null !== $lastOrderDate) {
                $data['lastOrderDate'] = $lastOrderDate;
            }

            $data = $this->getCustomerBirthday($customer->getDob(), $data);
        }

        return $data;
    }

    /**
     * @param int $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    private function getCustomerById(int $customerId): ?\Magento\Customer\Api\Data\CustomerInterface
    {
        if ($customerId > 0) {
            $loadingCustomerId = $customerId;
        }

        if ($this->_customerSession->getCustomerId() > 0 && $this->_customerSession->isLoggedIn()) {
            $loadingCustomerId = $this->_customerSession->getCustomerId();
        }

        if (isset($loadingCustomerId)) {
            try {
                $customer = $this->_customerRepository->getById($loadingCustomerId);
            } catch (NoSuchEntityException $noSuchEntityException) {
                $this->logger->info('Could not load customer with id: ' . $loadingCustomerId, []);
            } catch (LocalizedException $localizedException) {
                $this->logger->critical(
                    'ErrorMessage: ' . $localizedException->getMessage(),
                    ['Stacktrace' => $localizedException->getTraceAsString()]
                );
            }
        }

        return $customer ?? null;
    }

    /**
     * @param $customerId
     *
     * @return null|string
     */
    private function getLastOrderDate($customerId): ?string
    {
        $collection = $this->orderCollectionFactory->create();
        $collection
            ->addFieldToFilter('customer_id', ['eq' => $customerId])
            ->addFieldToSelect('created_at')
            ->addOrder('created_at')
            ->setPageSize(1);

        if ($collection->getSize() > 0) {
            /** @var Order $order */
            $order = $collection->getFirstItem();

            if ($order) {
                $date = $order->getCreatedAt();
                if (!empty($date)) {
                    $lastOrderDate = $this->formatOrderDate($date);
                }
            }
        }

        return $lastOrderDate ?? null;
    }

    /**
     * @param $date
     *
     * @return string
     */
    private function formatOrderDate($date): string
    {
        $date = date_create($date);
        $date->setTimezone(new DateTimeZone('UTC'));
        $datetime = $this->getAttributeConfig();

        if ($datetime) {
            $date = date_format($date, self::FORMAT_DATE_TIME);
        } else {
            $date = date_format($date, self::FORMAT_DATE_ONLY);
        }

        return $date;
    }

    /**
     * @return bool
     */
    private function getAttributeConfig(): bool
    {
        $attributes = json_decode($this->_sysConfig->getAttributesConfig(), true);

        if (isset($attributes['lastOrderDate'])) {
            return $attributes['lastOrderDate'] === RequestRecipientAttributes::LIST_TYPE_DATE_AND_TIME;
        }

        return false;
    }

    /**
     * @param $customerDob
     * @param array $data
     * @return array
     */
    private function getCustomerBirthday($customerDob, array $data): array
    {
        if (null !== $customerDob) {
            $customerBirthday = $this->formatBirthday($customerDob);
            if (null !== $customerBirthday) {
                $data['birthday'] = $customerBirthday;
            }
        }

        return $data;
    }

    /**
     * @param $birthday
     * @return null|string
     */
    private function formatBirthday($birthday): ?string
    {
        try {
            $newDateFormat = date_format(date_create($birthday), self::FORMAT_DATE_ONLY);
        } catch (Exception $e) {
            $this->logger->critical(
                'ErrorMessage: ' . $e->getMessage(),
                ['Stacktrace' => $e->getTraceAsString()]
            );
        }

        return $newDateFormat ?? null;
    }

    /**
     * @param int $storeId
     *
     * @return array
     *
     * @throws LocalizedException
     */
    private function getStoreData(int $storeId): array
    {
        $data = [];

        /** @var StoreInterface $store */
        $store = $this->_storeManager->getStore($storeId);
        $data['websiteId'] = $store->getWebsiteId();
        $website = $this->_storeManager->getWebsite($data['websiteId']);

        $data['storeViewName'] = $store->getName();
        $data['storeViewId'] = $store->getCode();

        $data['websiteName'] = $website->getName();
        /** @var GroupInterface $storeView */
        $storeView = $this->_storeManager->getGroup($store->getStoreGroupId());

        $data['storeName'] = $storeView->getName();
        $data['storeCode'] = $storeView->getId();

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function cleanData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (false === is_array($value) && false === empty($value)) {
                $data[$key] = trim($value);
            } else if (true === is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    $data[$key][$key2] = empty($value2) ? $value2 : trim($value2);
                }
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getMapping(): array
    {
        $defaults = RequestSubscriptionRecipients::getStandardAttributes();
        unset($defaults['email']);
        $map = array_merge(RequestSubscriptionRecipients::getMapableAttributes(), $defaults);
        $addMap = $this->_sysConfig->getMapConfig();
        $result = [];

        if (!empty($addMap)) {
            foreach ($addMap as $attribute) {
                if (isset($attribute['inxAttrib'], $attribute['magAttrib']) &&
                    in_array($attribute['magAttrib'], $map, true)) {
                    $result[$attribute['inxAttrib']] = $attribute['magAttrib'];
                }
            }
        }

        return array_merge($defaults, $result);
    }
}
