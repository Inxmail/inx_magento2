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

namespace Flagbit\Inxmail\Model\Api;

/**
 * Interface ApiClientInterface
 *
 * @package Flagbit\Inxmail\Model\Api
 */
interface ApiClientInterface
{
    /**
     * @return mixed
     */
    public static function getApiClient();

    /**
     * @param array|null $header
     */
    public function setHeader(array $header = null);

    /**
     * @param string $method
     */
    public function setRequestMethod(string $method);

    /**
     * @param array $credentials
     */
    public function setCredentials(array $credentials);

    /**
     * @param string $requestUrl
     */
    public function setRequestUrl(string $requestUrl);

    /**
     * @param string $requestPath
     */
    public function setRequestPath(string $requestPath);

    /**
     * @param string $requestUrl
     * @param string $requestPath
     * @param string|null $header
     * @param array|null $credentials
     *
     * @return bool|string
     */
    public function getResource(
        string $requestUrl = '', string $requestPath = '',
        string $header = null, array $credentials = null
    );


    /**
     * @param string $requestUrl
     * @param string $requestPath
     * @param string|null $header
     * @param array|null $credentials
     * @param string
     *
     * @return bool|string
     */
    public function postResource(
        string $requestUrl = '', string $requestPath = '',
        string $header = null, array $credentials = null, string $postData = ''
    );

    /**
     * @param string $requestUrl
     * @param string $requestPath
     * @param string|null $header
     * @param array|null $credentials
     * @param string
     *
     * @return bool|string
     */
    public function putResource(
        string $requestUrl = '', string $requestPath = '',
        string $header = null, array $credentials = null, string $postData = ''
    );

    /**
     * @param string $requestUrl
     * @param string $requestPath
     * @param string|null $header
     * @param array|null $credentials
     *
     * @return bool|string
     */
    public function deleteResource(
        string $requestUrl = '', string $requestPath = '',
        string $header = null, array $credentials = null
    );

    /**
     * @return integer
     */
    public function getResponseStatusCode(): int;

    /**
     * @return array
     */
    public function getResponseHeader(): array;

    /**
     * @return string
     */
    public function getResponseBody(): string;
}
