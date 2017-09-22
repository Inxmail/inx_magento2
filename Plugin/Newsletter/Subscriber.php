<?php

namespace Flagbit\Inxmail\Plugin\Newsletter;

use Magento\Newsletter\Model\Subscriber as MageSubscriber;

/**
 * Don't send any newsletter-related emails.
 * These will all go out through our marketing platform.
 */
class Subscriber
{
    protected $_scopeConfig;
    protected $inxEnabled = false;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig){
        $this->_scopeConfig = $scopeConfig;

        $this->inxEnabled = $this->_scopeConfig->getValue(
            'inxmail/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param MageSubscriber $oSubject
     * @param callable $proceed
     */
    public function aroundSendConfirmationRequestEmail(MageSubscriber $oSubject, callable $proceed)
    {
        if ($this->inxEnabled){
            return;
        } else {
            $proceed();
        }
    }

    /**
     * @param MageSubscriber $oSubject
     * @param callable $proceed
     */
    public function aroundSendConfirmationSuccessEmail(MageSubscriber $oSubject, callable $proceed)
    {
        if ($this->inxEnabled){
            return;
        } else {
            $proceed();
        }
    }

    /**
     * @param MageSubscriber $oSubject
     * @param callable $proceed
     */
    public function aroundSendUnsubscriptionEmail(MageSubscriber $oSubject, callable $proceed)
    {
        if ($this->inxEnabled){
            return;
        } else {
            $proceed();
        }
    }
}
