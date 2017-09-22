<?php
namespace Flagbit\Inxmail\Model\Request;

interface RequestInterface {
    public function sendRequest();
    public function writeRequest();
    public function getResponseCode();
    public function getResponseArray();
    public function getResponseJson();

    public function setRequestData(string $requestData);
}
