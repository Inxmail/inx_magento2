<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @author Flagbit GmbH
 * @copyright Copyright © 2017-2025 Inxmail GmbH
 * @license Licensed under the Open Software License version 3.0 (https://opensource.org/licenses/OSL-3.0)
 *
 */

namespace Flagbit\Inxmail\Model\Request;

/**
 * Interface RequestInterface
 *
 * @package Flagbit\Inxmail\Model\Request
 */
interface RequestInterface {
    /**
     * @return array
     */
    public function sendRequest(): array;

    /**
     * @return int
     */
    public function writeRequest(): int;

    /**
     * @param int $id
     *
     * @return int
     */
    public function putRequest(int $id): int;

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
