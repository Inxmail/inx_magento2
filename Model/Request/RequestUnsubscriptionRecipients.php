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
 * Class RequestUnsubscriptionRecipients
 *
 * @package Flagbit\Inxmail\Model\Request
 */
class RequestUnsubscriptionRecipients extends AbstractRequest
{
    const REQUEST_PATH = 'events/unsubscriptions/';

    const REQUEST_PARAMETER_ID = 'id';

    const REQUEST_LIST_ID = 'listId';
    const REQUEST_START_DATE = 'startDate';
    const REQUEST_END_DATE = 'endDate';
    const REQUEST_TYPES = 'types';

    const EVENTS_SUCCESSFUL = array(
        'PENDING_UNSUBSCRIPTION',
        'PENDING_UNSUBSCRIPTION_DONE',
        'VERIFIED_UNSUBSCRIPTION',
        'MANUAL_UNSUBSCRIPTION',
        'DUPLICATE_UNSUBSCRIPTION'
    );

    const EVENTS_FAIL = array(
        'UNSUBSCRIPTION_TIMED_OUT',
        'UNSUBSCRIPTION_ID_NOT_VALID',
        'UNSUBSCRIPTION_EMAIL_MISSMATCH',
        'UNSUBSCRIPTION_VERIFICATION_BOUNCED',
        'UNSUBSCRIPTION_INTERNAL_ERROR'
    );

    /**
     * RequestUnsubscriptionRecipients constructor
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
        $client->setRequestPath(self::REQUEST_PATH.$this->_requestParam);
        $client->setRequestMethod(\Zend_Http_Client::GET);
        $client->setRequestUrl($this->_systemConfig->getApiUrl());
        // ToDo: remove dryrun
        $this->_response = $client->getResource('','',null,null, false);

        return json_decode($this->_response, true);
    }

    /**
     * @return int
     */
    public function writeRequest(): int
    {
        if (!empty($this->_requestData)) {
            $client = $this->getApiClient();
            $client->setCredentials($this->getCredentials());
            $client->setRequestPath(self::REQUEST_PATH . $this->_requestParam);
            $client->setRequestMethod(\Zend_Http_Client::POST);
            $client->setRequestUrl($this->_systemConfig->getApiUrl());

            $client->setPostData( $this->_requestData);
            // ToDo: remove dryrun
            $this->_response = $client->postResource('', '', null, null, '', false);

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
            'attributes' => array ()
        );
    }
}
