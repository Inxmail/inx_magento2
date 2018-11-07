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
