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
 * Class RequestRecipientAttributes
 *
 * @package Flagbit\Inxmail\Model\Request
 */
class RequestRecipientAttributes extends AbstractRequest
{
    const REQUEST_PATH = 'attributes/';

    const LIST_TYPE_TEXT = 'TEXT';
    const LIST_TYPE_DATE_AND_TIME = 'DATE_AND_TIME';
    const LIST_TYPE_DATE_ONLY = 'DATE_ONLY';
    const LIST_TYPE_TIME_ONLY = 'TIME_ONLY';
    const LIST_TYPE_INTEGER = 'INTEGER';
    const LIST_TYPE_FLOATING_POINT_NUMBER = 'FLOATING_POINT_NUMBER';
    const LIST_TYPE_BOOLEAN = 'BOOLEAN';

    const TEXT_LENGTH_MIN = 1;
    const TEXT_LENGTH_MAX = 255;

    /** GET paramete; defines if visible in GUI client, true by default */
    const PARAMETER_HIDDEN = 'hidden';
    /** name of new attribute */
    const PARAMETER_NAME = 'name';
    /** type of new attribute */
    const PARAMETER_TYPE = 'type';
    /** max length of text attributes */
    const PARAMETER_MAX_LENGTH = 'maxLength';

    /**
     * RequestRecipientAttributes constructor
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
        $this->_response = $client->getResource('','',null,null);

        $tmpResponse = json_decode($this->_response, true);
        $tmp = '';

        /** Paging for attributes #33*/
        while (isset($tmpResponse['_links']['next']['href']) && $tmpResponse['_links']['next']['href'] !== $tmp) {
            $tmp = $tmpResponse['_links']['next']['href'];

            $tmpPath = explode('?', $tmp);
            $client->setRequestPath('?'.$tmpPath[1] ?? '/');
            $client->setRequestUrl($tmpPath[0]);
            $this->_response = $client->getResource('','',null,null);
            $responseCode = $client->getResponseStatusCode();
            $tmpArray = json_decode($this->_response, true);

            // merge results and override link for comparison
            $tmpResponse['_embedded'] = array_merge_recursive($tmpArray['_embedded'], $tmpResponse['_embedded']);
            $tmpResponse['_links'] = $tmpArray['_links'];

            if ($tmp === '') {
                break;
            }
        }

        if (is_array($tmpResponse)) {
            $this->_response = json_encode($tmpResponse);
        }

        $tempJson = json_decode($this->_response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $validResponse = $tempJson;
        }

        return $validResponse ?? [];
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
            $client->setPostData( is_array($this->_requestData) ? json_encode($this->_requestData) : $this->_requestData);
            $this->_response = $client->postResource('', '', null, null, '');

            return $client->getResponseStatusCode();
        }

        return false;
    }

    /**
     * Returns minimal valid array for new attribute
     *
     * @return array
     */
    public function getStandardAttributeOptions(): array
    {
        return array(
            self::PARAMETER_NAME => '',
            self::PARAMETER_TYPE => ''
        );
    }
}
