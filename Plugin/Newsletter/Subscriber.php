<?php

namespace Flagbit\Inxmail\Plugin\Newsletter;

use Braintree\Exception;
use \Flagbit\Inxmail\Model\Request;
use \Flagbit\Inxmail\Model\Request\RequestFactory;
use \Flagbit\Inxmail\Model\Config\SystemConfig;
use \Flagbit\Inxmail\Helper\Config;
use \Flagbit\Inxmail\Helper\SubscriptionData;
use \Magento\Newsletter\Model\Subscriber as MageSubscriber;
use \Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Don't send any newsletter-related emails.
 * These will all go out through our marketing platform.
 */
class Subscriber
{
    /** @var  \Magento\Framework\App\Config\ScopeConfigInterface */
    private $_scopeConfig;
    /** @var \Flagbit\Inxmail\Helper\SubscriptionData */
    private $subscriptionDataHelper;
    /** @var \Flagbit\Inxmail\Model\Request */
    private $request;
    /** @var \Flagbit\Inxmail\Model\Config\SystemConfig */
    private $systemConfig;

    protected $inxEnabled = false;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SubscriptionData $subscriptionDataHelper,
        Request $request,
        Config $config,
        RequestFactory $factory
    ){
        $this->scopeConfig = $scopeConfig;
        $this->subscriptionDataHelper = $subscriptionDataHelper;
        $this->request = $request;
        $this->systemConfig = SystemConfig::getSystemConfig($config);
        $this->factory = $factory;

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
    public function afterSave(MageSubscriber $subscriber)
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
        $resCode = $subscribeRequest->getResponseCode();
        var_dump($response, $resCode);
        $response = $subscribeRequest->sendRequest();

//        die();

//        throw new Exception('bla');

        return $subscriber;
    }
}
