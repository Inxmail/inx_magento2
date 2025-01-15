<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @author Flagbit GmbH
 * @copyright Copyright © 2017-2025 Inxmail GmbH
 * @license Licensed under the Open Software License version 3.0 (https://opensource.org/licenses/OSL-3.0)
 *
 */

namespace Flagbit\Inxmail\Model;

use Flagbit\Inxmail\Model\Request\RequestBounces;
use Flagbit\Inxmail\Model\Request\RequestFactory;
use Flagbit\Inxmail\Model\Request\RequestImports;
use Flagbit\Inxmail\Model\Request\RequestLists;
use Flagbit\Inxmail\Model\Request\RequestRecipientAttributes;
use Flagbit\Inxmail\Model\Request\RequestRecipients;
use Flagbit\Inxmail\Model\Request\RequestSubscriptionRecipients;
use Flagbit\Inxmail\Model\Request\RequestUnsubscriptionRecipients;

/**
 * Class Request
 *
 * @package Flagbit\Inxmail\Model\Request
 */
class Request
{
    /** @var \Flagbit\Inxmail\Model\Request\RequestFactory $requestFactory */
    private $requestFactory;

    /**
     * Request constructor
     *
     * @param \Flagbit\Inxmail\Model\Request\RequestFactory $factory
     */
    public function __construct(RequestFactory $factory)
    {
        $this->requestFactory = $factory;
    }

    /**
     * @return \Flagbit\Inxmail\Model\Request\RequestLists
     */
    public function getListClient(): RequestLists
    {
        return $this->requestFactory->create(RequestLists::class);
    }

    /**
     * @return \Flagbit\Inxmail\Model\Request\RequestRecipientAttributes
     */
    public function getAttributesClient(): RequestRecipientAttributes
    {
        return $this->requestFactory->create(RequestRecipientAttributes::class);
    }

    /**
     * @return \Flagbit\Inxmail\Model\Request\RequestRecipients
     */
    public function getRecipientsClient(): RequestRecipients
    {
        return $this->requestFactory->create(RequestRecipients::class);
    }

    /**
     * @return \Flagbit\Inxmail\Model\Request\RequestSubscriptionRecipients
     */
    public function getSubscriptionsClient(): RequestSubscriptionRecipients
    {
        return $this->requestFactory->create(RequestSubscriptionRecipients::class);
    }

    /**
     * @return \Flagbit\Inxmail\Model\Request\RequestUnsubscriptionRecipients
     */
    public function getUnsubscriptionsClient(): RequestUnsubscriptionRecipients
    {
        return $this->requestFactory->create(RequestUnsubscriptionRecipients::class);
    }

    /**
     * @return \Flagbit\Inxmail\Model\Request\RequestImports
     */
    public function getImportClient(): RequestImports
    {
        return $this->requestFactory->create(RequestImports::class);
    }

    /**
     * @return \Flagbit\Inxmail\Model\Request\RequestBounces
     */
    public function getBouncesClient(): RequestBounces
    {
        return $this->requestFactory->create(RequestBounces::class);
    }
}
