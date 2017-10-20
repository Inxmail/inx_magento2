<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @copyright Copyright (c) 2017 Flagbit GmbH
 */

namespace Flagbit\Inxmail\Model\Api;

/**
 * Class ApiClientFactory
 *
 * @package Flagbit\Inxmail\Model\Api
 */
class ApiClientFactory
{
    /**
     * @param string $class
     *
     * @return \Flagbit\Inxmail\Model\Api\ApiClientInterface
     */
    public function create(string $class = ''): ApiClientInterface
    {
        switch ($class) {
            case ApiClient::class:
                /** @var \Flagbit\Inxmail\Model\Api\ApiClient $result */
                $result = ApiClient::getApiClient();
                break;
            default:
                /** @var \Flagbit\Inxmail\Model\Api\ApiClient $result */
                $result = ApiClient::getApiClient();
        }

        return $result;
    }
}
