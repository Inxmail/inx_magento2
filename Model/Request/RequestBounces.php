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

namespace Flagbit\Inxmail\Model\Request;

use Flagbit\Inxmail\Helper\Config;
use Flagbit\Inxmail\Model\Api\ApiClientFactory;

/**
 * Class RequestBounces
 *
 * @package Flagbit\Inxmail\Model\Request
 */
class RequestBounces extends AbstractRequest
{
    const REQUEST_PATH = 'bounces/';

    const CATEGORY_BOUNCE_SOFT = 'SOFT';
    const CATEGORY_BOUNCE_HARD = 'HARD';
    const CATEGORY_BOUNCE_UNKNOWN = 'UNKNOWN';

    const REQUEST_LIST_ID = 'listId';
    const REQUEST_START_DATE = 'startDate';
    const REQUEST_END_DATE = 'endDate';
    const REQUEST_CATEGORY = 'bounceCategory';

    /**
     * RequestBounces constructor
     *
     * @param Config $config
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
     * @param int $id
     * @param array $attributes
     *
     * @return mixed
     */
    public function requestWithAttributes(int $id, array $attributes): array
    {
        $this->_requestParam = implode('/', explode('/', $this->_requestParam));
        $this->_requestParam .= $id . '?' . implode('&', $attributes);
        return $this->sendRequest();
    }
}
