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

namespace Flagbit\Inxmail\Model\Config;

use Flagbit\Inxmail\Helper\Config as ConfigHelper;

/**
 * Class SystemConfig
 *
 * @package Flagbit\Inxmail\Model\Config
 */
class SystemConfig
{
    private const CONFIG_PATH = 'inxmail/general/';
    private const CONFIG_PATH_RESTAUTH = 'inxmail/restauth/';
    private const CONFIG_FIELD_URL = 'api_url';
    private const CONFIG_FIELD_USER = 'api_user';
    private const CONFIG_FIELD_PASSWORD = 'api_password';
    private const CONFIG_FIELD_LIST = 'api_listid';
    private const CONFIG_FIELD_DEBUG = 'debug';
    private const CONFIG_FIELD_INXMAIL_ENABLED = 'enable';
    private const CONFIG_FIELD_REST_USER = 'rest_user';
    private const CONFIG_FIELD_REST_PASSWORD = 'rest_password';
    private const CONFIG_FIELD_CUSTOMER_MAPPING = 'customer_mapping';

    /** Path for the rest api user  (inxmail/restauth/rest_user) */
    private const CONFIG_PATH_REST_USER = self::CONFIG_PATH_RESTAUTH . self::CONFIG_FIELD_REST_USER;
    /** Path for inxmail rest api password  (inxmail/restauth/rest_password) */
    private const CONFIG_PATH_REST_PASSWORD = self::CONFIG_PATH_RESTAUTH . self::CONFIG_FIELD_REST_USER;
    /** Saves attribute data */
    private const CONFIG_PATH_ATTRIBUTES = 'inxmail/rest/attributes';
    /** Contains the path of the inxmail configuration for the list to sync to */
    private const CONFIG_PATH_CUSTOMER_MAPPING = 'inxmail/mapcustomer/mapping';
    /**
     * @var SystemConfig
     */
    protected static $_config;
    /**
     * @var ConfigHelper|null
     */
    protected $_helper;
    /**
     * @var array
     */
    protected $_data = [];

    /**
     * SystemConfig constructor.
     *
     * @param ConfigHelper $helper
     */
    protected function __construct(
        ConfigHelper $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Singleton
     *
     * @param ConfigHelper $helper
     *
     * @return SystemConfig
     */
    public static function getSystemConfig(ConfigHelper $helper): SystemConfig
    {
        if (self::$_config === null) {
            self::$_config = new self($helper);
        }

        return self::$_config;
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->getDataString(self::CONFIG_FIELD_URL);
    }

    /**
     * @param string $configField
     * @param string $configPath
     * @return string
     */
    private function getDataString(
        string $configField,
        string $configPath = self::CONFIG_PATH
    ): string {
        if (empty($this->_data[$configField])) {
            $this->_data[$configField] = $this->_helper->getConfig($configPath . $configField);
        }

        if (isset($this->_data[$configField]) && false === empty($this->_data[$configField])) {
            $data = (string) $this->_data[$configField];
        }

        return $data ?? '';
    }

    /**
     * @return string
     */
    public function getApiUser(): string
    {
        return $this->getDataString(self::CONFIG_FIELD_USER);
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->getApiPassword(self::CONFIG_FIELD_PASSWORD);
    }

    /**
     * @param string $config
     * @return string
     */
    private function getApiPassword(string $config): string
    {
        if (empty($this->_data[$config])) {
            $val = $this->_helper->getConfig(self::CONFIG_PATH . $config);
            if ($val) {
                $this->_data[$config] = $this->_helper->getEncryptor()->decrypt($val);
            }
        }

        if (isset($this->_data[$config]) && false === empty($this->_data[$config])) {
            $data = (string) $this->_data[$config];
        }

        return $data ?? '';
    }

    /**
     * @return string
     */
    public function getApiList(): string
    {
        return $this->getDataString(self::CONFIG_FIELD_LIST);
    }

    /**
     * @return bool
     */
    public function getDebug(): bool
    {
        return $this->getDataBool(self::CONFIG_FIELD_DEBUG);
    }

    /**
     * @param string $config
     * @return bool
     */
    private function getDataBool(string $config): bool
    {
        if (empty($this->_data[$config])) {
            $this->_data[$config] = $this->_helper->getConfig(self::CONFIG_PATH . $config);
        }

        if (isset($this->_data[$config]) && false === empty($this->_data[$config])) {
            $data = (bool) $this->_data[$config];
        }

        return $data ?? false;
    }

    /**
     * @return array
     */
    public function getMapConfig(): array
    {
        if (empty($this->_data[self::CONFIG_FIELD_CUSTOMER_MAPPING])) {
            $this->_data[self::CONFIG_FIELD_CUSTOMER_MAPPING] = $this->_helper->getConfig(self::CONFIG_PATH_CUSTOMER_MAPPING);
        }

        if($this->_data[self::CONFIG_FIELD_CUSTOMER_MAPPING] === '') {
            return [];
        }

        if (interface_exists(\Magento\Framework\Serialize\SerializerInterface::class)) {
            return \json_decode($this->_data[self::CONFIG_FIELD_CUSTOMER_MAPPING], true) ?? [];
        }

        return unserialize($this->_data[self::CONFIG_FIELD_CUSTOMER_MAPPING]) ?? [];
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->getDataBool(self::CONFIG_FIELD_INXMAIL_ENABLED);
    }

    /**
     * @return string
     */
    public function getRestApiUser(): string
    {
        return $this->getDataString(self::CONFIG_FIELD_REST_USER, self::CONFIG_PATH_REST_USER);
    }

    /**
     * @return string
     */
    public function getRestApiPassword(): string
    {
        return $this->getDataString(self::CONFIG_FIELD_REST_PASSWORD, self::CONFIG_PATH_REST_PASSWORD);
    }

    /**
     * @return string
     */
    public function getAttributesConfig(): string
    {
        return $this->getDataString(self::CONFIG_PATH_ATTRIBUTES, self::CONFIG_PATH_ATTRIBUTES);
    }
}
