<?php
namespace Flagbit\Inxmail\Model\Api;

class ApiClientFactory
{
    public function create(string $class = '')
    {
        $result = false;

        switch($class){
            case ApiClient::class:
                $result = ApiClient::getApiClient();
                break;
            default:
                $result = ApiClient::getApiClient();
        }

        return $result;
    }
}
