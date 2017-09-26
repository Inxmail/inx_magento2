<?php
namespace Flagbit\Inxmail\Model;

/**
 * Class Request
 * @package Flagbit\Inxmail\Model\Request
 */
class Request
{
    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * Request constructor.
     * @param RequestFactory $factory
     */
    public function __construct(RequestFactory $factory)
    {
        $this->requestFactory = $factory;
    }

    /**
     * @return RequestLists
     */
    public function getListClient(): RequestLists
    {
        return $this->requestFactory->create(RequestLists::class);
    }

    /**
     * @return RequestRecipientAttributes
     */
    public function getAttributesClient(): RequestRecipientAttributes
    {
        return $this->requestFactory->create(RequestRecipientAttributes::class);
    }

    /**
     * @return RequestRecipients
     */
    public function getRecipientsClient(): RequestRecipients
    {
        return $this->requestFactory->create(RequestRecipients::class);
    }

    /**
     * @return RequestSubscriptionRecipients
     */
    public function getSubscriptionsClient(): RequestSubscriptionRecipients
    {
        return $this->requestFactory->create(RequestSubscriptionRecipients::class);
    }

    /**
     * @return RequestUnsubscriptionRecipients
     */
    public function getUnsubscriptionsClient(): RequestUnsubscriptionRecipients
    {
        $this->requestFactory->create(RequestUnsubscriptionRecipients::class);
    }

    /**
     * @return RequestImports
     */
    public function getImportClient(): RequestImports
    {
        $this->requestFactory->create(RequestImports::class);
    }

    /**
     * @return RequestBounces
     */
    public function getBouncesClient(): RequestBounces
    {
        $this->requestFactory->create(RequestBounces::class);
    }
}
