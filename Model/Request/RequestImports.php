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
 * Class RequestImports
 *
 * @package Flagbit\Inxmail\Model\Request
 */
class RequestImports extends AbstractRequest
{
    const REQUEST_PATH = '/imports/recipients/';
    const REQUEST_PATH_OBSERVE = '/imports/recipients/{importId}';
    const REQUEST_PATH_OBSERVE_FILE_IMPORT = '/imports/recipients/{importId}/files/';

    const OBSERVE_FILE_IMPORT_STATE_NEW = 'NEW';
    const OBSERVE_FILE_IMPORT_STATE_PREFETCHING = 'PREFETCHING';
    const OBSERVE_FILE_IMPORT_STATE_READY_TO_PROCESS = 'READY_TO_PROCESS';
    const OBSERVE_FILE_IMPORT_STATE_PREFETCH_FAILED = 'PREFETCH_FAILED';
    const OBSERVE_FILE_IMPORT_STATE_PROCESSING = 'PROCESSING';
    const OBSERVE_FILE_IMPORT_STATE_SUCCESSFUL = 'SUCCESSFUL';
    const OBSERVE_FILE_IMPORT_STATE_FAILED = 'FAILED';
    const OBSERVE_FILE_IMPORT_STATE_DEQUEUED = 'DEQUEUED';
    const OBSERVE_FILE_IMPORT_STATE_CANCELED = 'CANCELED';

    const OBSERVE_STATUS_NEW = 'NEW';
    const OBSERVE_STATUS_PROCESSING = 'PROCESSING';
    const OBSERVE_STATUS_SUCCESS = 'SUCCESS';
    const OBSERVE_STATUS_FAILED = 'FAILED';
    const OBSERVE_STATUS_CANCELED = 'CANCELED';

    const OBSERVE_ERROR_COLUMN_NOT_FOUND_WARN = 'COLUMN_NOT_FOUND_WARN';
    const OBSERVE_ERROR_IGNORED_TRACKING_PERMISSION_WARN = 'IGNORED_TRACKING_PERMISSION_WARN';
    const OBSERVE_ERROR_SAVE_ERROR = 'SAVE_ERROR';
    const OBSERVE_ERROR_UNSUBSCRIPTION_ERROR = 'UNSUBSCRIPTION_ERROR';
    const OBSERVE_ERROR_BLACKLIST_ERROR = 'BLACKLIST_ERROR';
    const OBSERVE_ERROR_IGNORED_IMPORT_ERROR = 'IGNORED_IMPORT_ERROR';
    const OBSERVE_ERROR_IMPORT_CRASHED_ERROR = 'IMPORT_CRASHED_ERROR';
    const OBSERVE_ERROR_DELETE_RECIPIENTS_ERROR = 'DELETE_RECIPIENTS_ERROR';
    const OBSERVE_ERROR_LINE_PARSE_ERROR = 'LINE_PARSE_ERROR';
    const OBSERVE_ERROR_ATTRIBUTE_PARSE_ERROR = 'ATTRIBUTE_PARSE_ERROR';
    const OBSERVE_ERROR_EMAIL_PARSE_ERROR = 'EMAIL_PARSE_ERROR';
    const OBSERVE_ERROR_HEADER_ERROR = 'HEADER_ERROR';

    const REQUEST_PARAMETER_LIST_ID = 'listId';
    const REQUEST_PARAMETER_RESUBSCRIBE = 'resubscribe';
    const REQUEST_PARAMETER_TRUNCATE = 'truncate';

    /** @var string */
    private $_file;
    /** @var bool */
    private $isCompressed = false;

    /**
     * RequestImports constructor
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
        $client->setRequestMethod(\Laminas\Http\Request::METHOD_GET);
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
            $client->setRequestMethod(\Laminas\Http\Request::METHOD_POST);

            if ($this->isCompressed) {
                $this->setHeaderCompressed($client);
            } else {
                $this->setHeaderCsv($client);
            }

            $client->setRequestUrl($this->_systemConfig->getApiUrl());
            $client->setPostData($this->_file);
            $this->_response = $client->postResource('', '', null, null, '');

            return $client->getResponseStatusCode();
        }

        return 0;
    }

    /**
     * @param array $recipients
     */
    public function setRequestFile(array $recipients)
    {
        $csvData = '';
        foreach ($recipients as $value) {
            array_walk($value, function (&$item) {
                $item = '"' . $item . '"';
            });
            $csvData .= implode(';', $value) . PHP_EOL;
        }

        // form field separator
        $delimiter = '----' . 'Inxmail';
        $data = '';

        $data .= '--' . $delimiter . "\r\n";
        $data .= 'Content-Disposition: form-data; name="file"; filename="datafile.csv"' . "\r\n";
        $data .= 'Content-Type: text/csv' . "\r\n";
        $data .= "\r\n";
        $data .= $csvData . "\r\n";
        $data .= '--' . $delimiter . '--';

        $this->_file = $data;
    }

    /**
     * @param array $recipients
     */
    public function setRequestFileGz(array $recipients)
    {
        $csvData = '';
        foreach ($recipients as $value) {
            array_walk($value, function (&$item) {
                $item = '"' . $item . '"';
            });
            $csvData .= implode(';', $value) . PHP_EOL;
        }

        // form field separator
        $delimiter = '----' . 'Inxmail';
        $data = '';

        $data .= '--' . $delimiter . "\r\n";
        $data .= 'Content-Disposition: form-data; name="file"; filename="datafile.csv.gz"' . "\r\n";
        $data .= 'Content-Encoding: gzip' . "\r\n";
        $data .= 'Content-Type: application/gzip' . "\r\n";
        $data .= "\r\n";
        $data .= gzencode($csvData, 9) . "\r\n";
        $data .= '--' . $delimiter . '--';

        $this->_file = $data;
        $this->isCompressed = true;
    }

    /**
     * @param $client
     */
    private function setHeaderCompressed($client)
    {
        $header = [
            'Accept: application/hal+json,application/problem+json',
            'Content-Disposition: form-data; name="file"; filename="datafile.csv.gz"',
            'Content-Type: multipart/form-data; boundary=----Inxmail'
        ];

        $client->setHeader($header);
    }

    /**
     * @param $client
     */
    private function setHeaderCsv($client)
    {
        $header = [
            'Accept: application/hal+json,application/problem+json',
            'Content-Disposition: form-data; name="file"; filename="datafile.csv"',
            'Content-Type: multipart/form-data; boundary=----Inxmail'
        ];

        $client->setHeader($header);
    }
}
