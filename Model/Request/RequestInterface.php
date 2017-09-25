<?php
namespace Flagbit\Inxmail\Model\Request;

/**
 * Interface RequestInterface
 * @package Flagbit\Inxmail\Model\Request
 */
interface RequestInterface {
    /**
     * @return array
     */
    public function sendRequest();

    /**
     * @return array
     */
    public function writeRequest();

    /**
     * @param int $id
     * @return bool|int
     */
    public function putRequest(int $id);

    /**
     * @param int $id
     * @return bool|int
     */
    public function deleteRequest(int $id);

    /**
     * @return int
     */
    public function getResponseCode(): int;

    /**
     * @return array
     */
    public function getResponseArray(): array;

    /**
     * @return string
     */
    public function getResponseJson(): string;

    /**
     * @param string $requestData
     */
    public function setRequestData(string $requestData);
}
