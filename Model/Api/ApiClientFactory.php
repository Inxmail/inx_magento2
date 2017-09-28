<?php
namespace Flagbit\Inxmail\Model\Api;

/**
 * Class ApiClientFactory
 * @package Flagbit\Inxmail\Model\Api
 */
class ApiClientFactory
{
    /**
     * @param string $class
     * @return \Flagbit\Inxmail\Model\Api\ApiClientInterface
     */
    public function create(string $class = '')
    {
        switch($class){
            case \Flagbit\Inxmail\Model\Api\ApiClient::class:
                $result = ApiClient::getApiClient();
                break;
            default:
                $result = ApiClient::getApiClient();
        }

        return $result;
    }
}
