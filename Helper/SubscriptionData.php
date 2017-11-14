<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @copyright Copyright (c) 2017 Flagbit GmbH
 */

namespace Flagbit\Inxmail\Helper;

use \Flagbit\Inxmail\Model\Request\RequestSubscriptionRecipients;
use \Flagbit\Inxmail\Model\Config\SystemConfig;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Customer\Model\ResourceModel\CustomerRepository;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Newsletter\Model\Subscriber;

/**
 * Class Config
 *
 * @package Flagbit\Inxmail\Helper
 */
class SubscriptionData extends AbstractHelper
{

    /** @var \Magento\Customer\Model\ResourceModel\CustomerRepository */
    protected $_customerRepository;
    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /** @var \Flagbit\Inxmail\Model\Config\SystemConfig */
    protected $_sysConfig;

    /**
     * Config constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Flagbit\Inxmail\Helper\Config $config
     */
    public function __construct(
        Context $context,
        CustomerRepository $customerRepository,
        StoreManagerInterface $storeManager,
        Config $config
    )
    {
        $this->_customerRepository = $customerRepository;
        $this->_storeManager = $storeManager;
        $this->_sysConfig = SystemConfig::getSystemConfig($config);

        parent::__construct($context);
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSubscriptionFields(Subscriber $subscriber): array
    {
        $data = $this->getSubscriptionStaticData($subscriber);
        $data = $this->cleanData($data);

        $map = $this->getMapping();

        $result = array();
        foreach ($map as $inxKey => $magKey) {
            if ($inxKey === 'email') {
                continue;
            }
            $keys = array_keys($data);
            if (in_array($magKey, $keys, true) && isset($data[$magKey])) {
                $result[$inxKey] = $data[$magKey];
            }
        }

        return $result;
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
        $result = array();

        if (!empty($addMap)) {
            foreach ($addMap as $attribute) {
                if (in_array($attribute['magAttrib'], $map, true)) {
                    $result[$attribute['inxAttrib']] = $attribute['magAttrib'];
                }
            }
        }

        return array_merge($defaults, $result);
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSubscriptionStaticData(Subscriber $subscriber): array
    {

        $data = array();
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
     * @param int $storeId
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getStoreData(int $storeId): array
    {
        $data = array();

        $store = $this->_storeManager->getStore($storeId);
        $data['websiteId'] = $store->getWebsiteId();
        $website = $this->_storeManager->getWebsite($data['websiteId']);

        $data['storeName'] = $store->getName();
        $data['storeCode'] = $store->getCode();

        $data['websiteName'] = $website->getName();
        $storeView = $this->_storeManager->getDefaultStoreView();
        $data['storeViewName'] = $storeView->getName();
        $data['storeViewId'] = $storeView->getId();

        return $data;
    }

    /**
     * @param int $customerId
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomerData(int $customerId): array
    {
        $data = array();
        if ($customerId > 0) {
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $this->_customerRepository->getById($customerId);
            $data['firstName'] = $customer->getFirstname();
            $data['lastName'] = $customer->getLastname();
            $data['birthday'] = $customer->getDob();
            try {
                $data['birthday'] = $data['birthday'] ? date_format(date_create($data['birthday']), 'Y-m-d') : '';
            } catch (\Exception $e) {
                $data['birthday'] = '';
            }
            $data['gender'] = $customer->getGender();
            $data['group'] = $customer->getGroupId();
            $data['prefix'] = $customer->getPrefix();
        }

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
            $arr = is_array($value);
            if (!$arr && !empty($value)) {
                $data[$key] = trim($value);
            } else if ($arr) {
                foreach ($value as $key2 => $value2) {
                    $data[$key][$key2] = empty($value2) ?? trim($value2);
                }
            }
        }

        return $data;
    }
}
