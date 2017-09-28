<?php

namespace Flagbit\Inxmail\Plugin\Newsletter;

use \Flagbit\Inxmail\Model\Request;
use \Flagbit\Inxmail\Logger\Logger;
use \Flagbit\Inxmail\Model\Request\RequestFactory;
use \Flagbit\Inxmail\Model\Config\SystemConfig;
use \Flagbit\Inxmail\Helper\Config;
use \Flagbit\Inxmail\Helper\SubscriptionData;
use \Magento\Newsletter\Model\Subscriber as MageSubscriber;
use \Magento\Framework\App\Config\ScopeConfigInterface;


/**
 * Class for Subscriber event handling
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

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SubscriptionData $subscriptionDataHelper,
        Request $request,
        Config $config,
        RequestFactory $factory,
        Logger $logger
    ){
        $this->subscriptionDataHelper = $subscriptionDataHelper;
        $this->request = $request;
        $this->systemConfig = SystemConfig::getSystemConfig($config);
        $this->factory = $factory;
        $this->logger = $logger;

        $this->inxEnabled = $this->scopeConfig->getValue(
            'inxmail/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param callable $proceed
     */
    public function aroundSendConfirmationRequestEmail(MageSubscriber $subscriber, callable $proceed)
    {
        if ($this->inxEnabled){
            return;
        } else {
            $proceed();
        }
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param callable $proceed
     */
    public function aroundSendConfirmationSuccessEmail(MageSubscriber $subscriber, callable $proceed)
    {
        if ($this->inxEnabled){
            return;
        } else {
            $proceed();
        }
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param callable $proceed
     */
    public function aroundSendUnsubscriptionEmail(MageSubscriber $subscriber, callable $proceed)
    {
        if ($this->inxEnabled){
            return;
        } else {
            $proceed();
        }
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @return null
     */
    public function afterSave(MageSubscriber $subscriber): MageSubscriber
    {
//         FixMe: changeStatus true as requested by Ticket
//        $changedStatus = $subscriber->isStatusChanged();
        $changedStatus = true;

        $status = $subscriber->getStatus();

        try {
            if ($changedStatus) {
                if ($status === MageSubscriber::STATUS_UNSUBSCRIBED) {
                    $this->unsubscribeInxmail($subscriber);
                } else {
                    $this->subscribeInxmail($subscriber);
                }
            }
        } catch (Exception $e) {
            $this->logger->critical('Errormessage: '.$e->getMessage(), $e);
        }

        return $subscriber;
    }


    /**
     * @param MageSubscriber $subscriber
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
                'Not Subscribed: '.$reqData['email'].
                str_replace('%s', isset($result['type']) ?? 'Undefined', 'Inxmail API Error: %s'),
                array($reqData, $result)
            );
        }

        return (int)$response;
    }

    /**
     * @param MageSubscriber $subscriber
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
                'Not Unsubscribed: '.$reqData['email'].
                str_replace('%s', isset($result['type']) ?? 'Undefined', 'Inxmail API Error: %s'),
                array($reqData, $result)
            );
        }

         return (int)$response;
    }
}
