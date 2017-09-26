<?php
namespace Flagbit\Inxmail\Model\Request;

use Flagbit\Inxmail\Helper\Config;
use Flagbit\Inxmail\Model\Api\ApiClientFactory;

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

    private $_file;
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
        $this->_response = $client->getResource('','',null,null, false);

        return json_decode($this->_response, true);
    }

    public function writeRequest()
    {
        if (!empty($this->_requestData) || !empty($this->_file)) {
            $client = $this->getApiClient();
            $client->setCredentials($this->getCredentials());
            $client->setRequestPath(self::REQUEST_PATH.$this->_requestParam);
            $client->setRequestMethod(\Zend_Http_Client::POST);
            $header = array(
                'Accept: application/hal+json,application/problem+json',
                'Content-Disposition: form-data; name="file"; filename="datafile.csv"',
                'Content-Type: multipart/form-data; boundary=----Inxmail'
            );
            $client->setHeader($header);
            $client->setRequestUrl($this->_systemConfig->getApiUrl());
//            $client->setPostData( 'file='. (is_array($this->_requestData) ? implode(PHP_EOL, $this->_requestData) : $this->_requestData));
            var_dump($this->_file);
            $client->setPostData( $this->_file);
            $this->_response = $client->postResource('', '', null, null, '', false);

            var_dump($this->_response);
            return $client->getResponseStatusCode();
        }

        return false;
    }

    /**
     * Returns minimal valid array for new list
     *
     * @return array
     */
    public function getStandardListOptions(): array
    {
        return array(
            self::PARAMETER_NAME => '',
            self::PARAMETER_TYPE => self::LIST_TYPE_STANDARD,
            self::PARAMETER_SENDER_ADDRESS => ''
        );
    }

    public function setRequestFile(array $recipients) {
        $csvData = '';
        foreach ($recipients as $value){
            array_walk($value, function(&$item, $key){
                $item = '"'.$item.'"';
            });
            $csvData .= implode(';', $value).PHP_EOL;
        }

        // form field separator
        $delimiter = '----' . 'Inxmail';
        $data = '';

        $data .= "--" . $delimiter . "\r\n";
        $data .= 'Content-Disposition: form-data; name="file"; filename="datafile.csv"' . "\r\n";
        $data .= 'Content-Type: text/csv' . "\r\n";
        $data .= "\r\n";
        $data .= $csvData . "\r\n";
        $data .= "--" . $delimiter . "--";

        $this->_file = $data;
    }
}
