<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @copyright Copyright (c) 2017 Flagbit GmbH
 */

namespace Flagbit\Inxmail\Plugin\Newsletter;

use \Flagbit\Inxmail\Model\Request;
use \Flagbit\Inxmail\Logger\Logger;
use \Flagbit\Inxmail\Model\Config\SystemConfig;
use \Flagbit\Inxmail\Helper\Config;
use \Flagbit\Inxmail\Helper\SubscriptionData;
use \Magento\Newsletter\Model\Subscriber as MageSubscriber;
use \Magento\Newsletter\Model\ResourceModel\Subscriber as MageSubscriberResource;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Request\Http;


/**
 * Class Subscriber
 *
 * @package Flagbit\Inxmail\Plugin\Newsletter
 */
class Subscriber
{
    /** @var \Flagbit\Inxmail\Helper\SubscriptionData */
    private $subscriptionDataHelper;
    /** @var \Flagbit\Inxmail\Model\Request */
    private $request;
    /** @var \Flagbit\Inxmail\Model\Config\SystemConfig */
    private $systemConfig;
    /** @var \Flagbit\Inxmail\Logger\Logger */
    private $logger;
    /** @var bool */
    protected $inxEnabled = false;
    /** @var \Magento\Framework\App\Request\Http */
    private $httpRequest;
    /** @var \Magento\Newsletter\Model\ResourceModel\Subscriber */
    private $subscriberResource;

    /**
     * Subscriber constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Flagbit\Inxmail\Helper\SubscriptionData $subscriptionDataHelper
     * @param \Flagbit\Inxmail\Model\Request $request
     * @param \Flagbit\Inxmail\Helper\Config $config
     * @param \Flagbit\Inxmail\Logger\Logger $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        MageSubscriberResource $subscriberResource,
        SubscriptionData $subscriptionDataHelper,
        Request $request,
        Config $config,
        Logger $logger,
        Http $httpRequest
    )
    {
        $this->subscriptionDataHelper = $subscriptionDataHelper;
        $this->request = $request;
        $this->systemConfig = SystemConfig::getSystemConfig($config);
        $this->logger = $logger;

        $this->httpRequest = $httpRequest;
        $this->subscriberResource = $subscriberResource;

        $this->inxEnabled = $scopeConfig->getValue(
            'inxmail/general/enable',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param callable $proceed
     * @param array $args
     */
    public function aroundSendConfirmationRequestEmail(MageSubscriber $subscriber, callable $proceed, array ...$args)
    {
        if ($this->inxEnabled) {
            return;
        } else {
            return $proceed(...array_values($args));
        }
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param callable $proceed
     * @param array $args
     */
    public function aroundSendConfirmationSuccessEmail(MageSubscriber $subscriber, callable $proceed, array ...$args)
    {
        if ($this->inxEnabled) {
            return;
        } else {
            return $proceed(...array_values($args));
        }
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param callable $proceed
     * @param array $args
     */
    public function aroundSendUnsubscriptionEmail(MageSubscriber $subscriber, callable $proceed, array ...$args)
    {
        if ($this->inxEnabled) {
            return;
        } else {
            return $proceed(...array_values($args));
        }
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     *
     * @return \Magento\Newsletter\Model\Subscriber
     */
    public function afterSave(MageSubscriber $subscriber): MageSubscriber
    {
        /** if not enabled or is confirm action #31 */
        if (!$this->inxEnabled || !empty($this->httpRequest->get('code'))) {
            return $subscriber;
        }

        $changedStatus = $subscriber->isStatusChanged();
        $customerId = $subscriber->getCustomerId() ?? null;
        $status = $subscriber->getStatus();


        /** explicit subscribe call request (guest) as requested #1 */
        if (!$changedStatus && ( $customerId === null || $customerId < 1) && $status === MageSubscriber::STATUS_SUBSCRIBED) {
            $changedStatus = true;
        }

        /** prevent subscribe request on customer save #32 */
        if (!$changedStatus && $customerId > 0) {
            $changedStatus = false;
        }

        try {
            if ($changedStatus) {
                if ($status === MageSubscriber::STATUS_UNSUBSCRIBED) {
                    $this->unsubscribeInxmail($subscriber);
                } else {
                    $this->subscribeInxmail($subscriber);
                }
            }
        } catch (Exception $e) {
            $this->logger->critical('ErrorMessage: ' . $e->getMessage(), $e);
        }

        return $subscriber;
    }


    /**
     * @param MageSubscriber $subscriber
     *
     * @return int
     */
    private function subscribeInxmail(MageSubscriber $subscriber): int
    {
        $attribData = $this->subscriptionDataHelper->getSubscriptionFields($subscriber);

        /** @var \Flagbit\Inxmail\Model\Request\RequestSubscriptionRecipients */
        $subscribeRequest = $this->request->getSubscriptionsClient();

        $listId = $this->systemConfig->getApiList();
        $reqData = array(
            'email' => $subscriber->getEmail(),
            'listId' => $listId,
            'attributes' => $attribData
        );

        $subscribeRequest->setRequestData(json_encode($reqData));
        $response = $subscribeRequest->writeRequest();
        $result = $subscribeRequest->getResponseArray();

        if ($response === 200) {
            if ($this->systemConfig->getInxDebug()) {
                $this->logger->info(
                    'Subscribed: ' . $reqData['email'], array($reqData, $result)
                );
            }
        } else {
            $this->logger->alert(
                'Not Subscribed: ' . $reqData['email'] .
                str_replace('%s', isset($result['type']) ?? 'Undefined', 'Inxmail API Error: %s'),
                array($reqData, $result)
            );
            $subscriber->setSubscriberStatus(MageSubscriber::STATUS_UNSUBSCRIBED);
            $this->subscriberResource->save($subscriber);
        }

        return (int)$response;
    }

    /**
     * @param MageSubscriber $subscriber
     *
     * @return int
     */
    private function unsubscribeInxmail(MageSubscriber $subscriber): int
    {
        /** @var \Flagbit\Inxmail\Model\Request\RequestUnsubscriptionRecipients */
        $unsubscribeRequest = $this->request->getUnsubscriptionsClient();

        $reqData = array(
            'email' => $subscriber->getEmail(),
            'listId' => $this->systemConfig->getApiList()
        );

        $unsubscribeRequest->setRequestData(json_encode($reqData));

        $response = $unsubscribeRequest->writeRequest();
        $result = $unsubscribeRequest->getResponseArray();

        if ($response === 200) {
            if ($this->systemConfig->getInxDebug()) {
                $this->logger->info(
                    'Unsubscribed: ' . $reqData['email'], array($reqData, $result)
                );
            }
        } else {
            $this->logger->alert(
                'Not Unsubscribed: ' . $reqData['email'] .
                str_replace('%s', isset($result['type']) ?? 'Undefined', 'Inxmail API Error: %s'),
                array($reqData, $result)
            );
            $subscriber->setSubscriberStatus(MageSubscriber::STATUS_SUBSCRIBED);
            $this->subscriberResource->save($subscriber);
        }

        return (int)$response;
    }
}
