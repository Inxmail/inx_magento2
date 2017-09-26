<?php
namespace Flagbit\Inxmail\Model\Request;

use Flagbit\Inxmail\Helper\Config;
use Flagbit\Inxmail\Model\Api\ApiClientFactory;

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

    public function __construct(Config $config, ApiClientFactory $factory)
    {
        parent::__construct($config, $factory);
    }

    public function sendRequest()
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

    public function requestWithAttributes(int $id, array $attributes) {
        $this->_requestParam = implode('/',explode('/',$this->_requestParam));
        $this->_requestParam .= $id.'?'.implode('&',$attributes);
        return $this->sendRequest();
    }
}
