<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @copyright Copyright (c) 2017 Flagbit GmbH
 */

namespace Flagbit\Inxmail\Model\Request;

use Flagbit\Inxmail\Helper\Config;
use Flagbit\Inxmail\Model\Api\ApiClientFactory;

/**
 * Class RequestSubscriptionRecipients
 *
 * @package Flagbit\Inxmail\Model\Request
 */
class RequestSubscriptionRecipients extends AbstractRequest
{
    const REQUEST_PATH = 'events/subscriptions/';

    const REQUEST_PARAMETER_ID = 'id';

    const REQUEST_LIST_ID = 'listId';
    const REQUEST_START_DATE = 'startDate';
    const REQUEST_END_DATE = 'endDate';
    const REQUEST_TYPES = 'types';

    const EVENTS_SUCCESSFUL = array(
        'PENDING_SUBSCRIPTION',
        'PENDING_SUBSCRIPTION_DONE',
        'VERIFIED_SUBSCRIPTION',
        'MANUAL_SUBSCRIPTION',
        'DUPLICATE_SUBSCRIPTION'
    );

    const EVENTS_FAIL = array(
        'SUBSCRIPTION_TIMED_OUT',
        'SUBSCRIPTION_ID_NOT_VALID',
        'SUBSCRIPTION_EMAIL_MISSMATCH',
        'BLACKLISTED',
        'INVALID_ADDRESS_ERROR',
        'SUBSCRIPTION_VERIFICATION_BOUNCED',
        'SUBSCRIPTION_INTERNAL_ERROR'
    );

    /**
     * RequestSubscriptionRecipients constructor
     *
     * @param \Flagbit\Inxmail\Helper\Config $config
     * @param \Flagbit\Inxmail\Model\Api\ApiClientFactory $factory
     */
    public function __construct(Config $config, ApiClientFactory $factory)
    {
        parent::__construct($config, $factory);
    }

    /**
     * @return array
     */
    public function sendRequest(): array
    {
        $client = $this->getApiClient();
        $client->setCredentials($this->getCredentials());
        $client->setRequestPath(self::REQUEST_PATH . $this->_requestParam);
        $client->setRequestMethod(\Zend_Http_Client::GET);
        $client->setRequestUrl($this->_systemConfig->getApiUrl());

        $this->_response = $client->getResource('', '', null, null);

        return json_decode($this->_response, true);
    }

    /**
     * @return int
     */
    public function writeRequest(): int
    {
        if (!empty($this->_requestData) || !empty($this->_file)) {
            $client = $this->getApiClient();

            $client->setCredentials($this->getCredentials());
            $client->setRequestPath(self::REQUEST_PATH . $this->_requestParam);
            $client->setRequestMethod(\Zend_Http_Client::POST);
            $client->setHeader();
            $client->setRequestUrl($this->_systemConfig->getApiUrl());
            $client->setPostData($this->_requestData);

            $this->_response = $client->postResource('', '', null, null, '');

            return $client->getResponseStatusCode();
        }

        return 0;
    }

    /**
     * @return array
     */
    public function getStandardOptions(): array
    {
        return array(
            'listId' => 0,
            'email' => '',
            'attributes' => array()
        );
    }

    /**
     * @return array
     */
    public static function getStandardAttributes(): array
    {
        return array(
            'email' => 'email',
            'magentoSubscriberId' => 'subscriberId',
            'magentoSubscriberToken' => 'subscriberToken'
        );
    }

    /**
     * @return array
     */
    public static function getMapableAttributes(): array
    {
        return array(
            'Vorname' => 'firstName',
            'Nachname' => 'lastName',
            'magentoWebsiteName' => 'websiteName',
            'magentoWebsiteId' => 'websiteId',
            'magentoStoreName' => 'storeName',
            'magentoStoreViewName' => 'storeViewName',
            'Geburtsdatum' => 'birthday',
            'Geschlecht' => 'gender',
            'magentoStoreId' => 'storeId',
            'magentoStoreViewId' => 'storeViewId',
            'magentoCustomerGroup' => 'group'
        );
    }
}
