<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @copyright Copyright (c) 2017 Flagbit GmbH
 */

namespace Flagbit\Inxmail\Model\Config;

use \Flagbit\Inxmail\Helper\Config;

/**
 * Class SystemConfig
 *
 * @package Flagbit\Inxmail\Model\Config
 */
class SystemConfig
{
    /** Contains the path of the inxmail configuration for the api url */
    const CONFIG_PATH_URL = 'inxmail/general/api_url';
    /** Contains the path of the inxmail configuration for the api user */
    const CONFIG_PATH_API_USER = 'inxmail/general/api_user';
    /** Contains the path of the inxmail configuration for the api secret */
    const CONFIG_PATH_API_KEY = 'inxmail/general/api_password';
    /** Contains the path of the inxmail configuration for the list to sync to */
    const CONFIG_PATH_API_LIST = 'inxmail/general/api_listid';
    /** Contains the path of the inxmail configuration for debug setting */
    const CONFIG_PATH_DEBUG = 'inxmail/general/debug';
    /** Contains the path of the inxmail configuration for the list to sync to */
    const CONFIG_PATH_CUSTOMER_MAPPING = 'inxmail/mapcustomer/mapping';
    /** Contains the path of the inxmail configuration for module enabled or not */
    const CONFIG_PATH_ENABLED = 'inxmail/general/enable';
    /** Contains the path of the inxmail configuration for inxmail rest api user */
    const CONFIG_PATH_REST_USER = 'inxmail/restauth/rest_user';
    /** Contains the path of the inxmail configuration for inxmail rest api password */
    const CONFIG_PATH_REST_PASSWORD = 'inxmail/restauth/rest_password';

    /** Datafield key */
    const CONFIG_FIELD_URL = 'apiUrl';
    /** Datafield key */
    const CONFIG_FIELD_USER = 'apiUser';
    /** Datafield key */
    const CONFIG_FIELD_KEY = 'apiKey';
    /** Datafield key */
    const CONFIG_FIELD_LIST = 'apiList';
    /** Datafield key */
    const CONFIG_FIELD_DEBUG = 'debug';
    /** Datafield key */
    const CONFIG_FIELD_CUSTOMER_MAPPING = 'customerMapping';
    /** Datafield key */
    const CONFIG_FIELD_INXMAIL_ENABLED = 'enabled';
    /** Datafield key */
    const CONFIG_FIELD_REST_USER = 'restUser';
    /** Datafield key */
    const CONFIG_FIELD_REST_PASSWORD = 'restPassword';

    /** @var \Flagbit\Inxmail\Model\Config\SystemConfig */
    protected static $_config;
    /** @var \Flagbit\Inxmail\Helper\Config|null */
    protected $_helper;

    /** @var array */
    protected $_data = array();

    /**
     * SystemConfig constructor.
     *
     * @param \Flagbit\Inxmail\Helper\Config $helper
     */
    protected function __construct(Config $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * Singleton
     *
     * @param \Flagbit\Inxmail\Helper\Config $helper
     *
     * @return SystemConfig
     */
    public static function getSystemConfig(Config $helper): SystemConfig
    {
        if (self::$_config === null) {
            self::$_config = new self($helper);
        }

        return self::$_config;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $this->getApiUrl();
        $this->getApiUser();
        $this->getApiKey();
        $this->getApiList();

        return $this->_data;
    }

    /**
     * @param bool $refresh
     *
     * @return string
     */
    public function getApiUrl($refresh = false): string
    {
        if (empty($this->_data[self::CONFIG_FIELD_URL]) || $refresh) {
            $this->_data[self::CONFIG_FIELD_URL] = $this->_helper->getConfig(self::CONFIG_PATH_URL);
        }

        return $this->_data[self::CONFIG_FIELD_URL];
    }

    /**
     * @param bool $refresh
     *
     * @return string
     */
    public function getApiUser($refresh = false): string
    {
        if (empty($this->_data[self::CONFIG_FIELD_USER]) || $refresh) {
            $this->_data[self::CONFIG_FIELD_USER] = $this->_helper->getConfig(self::CONFIG_PATH_API_USER);
        }

        return $this->_data[self::CONFIG_FIELD_USER];
    }

    /**
     * @param bool $refresh
     *
     * @return string
     */
    public function getApiKey($refresh = false): string
    {
        if (empty($this->_data[self::CONFIG_FIELD_KEY]) || $refresh) {
            $this->_data[self::CONFIG_FIELD_KEY] = $this->_helper->getConfig(self::CONFIG_PATH_API_KEY);
        }

        return $this->_data[self::CONFIG_FIELD_KEY];
    }

    /**
     * @param bool $refresh
     *
     * @return string
     */
    public function getApiList($refresh = false): string
    {
        if (empty($this->_data[self::CONFIG_FIELD_LIST]) || $refresh) {
            $this->_data[self::CONFIG_FIELD_LIST] = $this->_helper->getConfig(self::CONFIG_PATH_API_LIST);
        }

        return $this->_data[self::CONFIG_FIELD_LIST];
    }

    /**
     * @return bool
     */
    public function getInxDebug(): bool
    {

        if (empty($this->_data[self::CONFIG_FIELD_DEBUG])) {
            $this->_data[self::CONFIG_FIELD_DEBUG] = $this->_helper->getConfig(self::CONFIG_PATH_DEBUG);
        }

        return (bool)$this->_data[self::CONFIG_FIELD_DEBUG];
    }

    /**
     * @return array
     */
    public function getMapConfig(): array
    {
        if (empty($this->_data[self::CONFIG_FIELD_CUSTOMER_MAPPING])) {
            $this->_data[self::CONFIG_FIELD_CUSTOMER_MAPPING] = $this->_helper->getConfig(self::CONFIG_PATH_CUSTOMER_MAPPING);
        }

        return unserialize($this->_data[self::CONFIG_FIELD_CUSTOMER_MAPPING]) ?? [];
    }

    /**
     * @return bool
     */
    public function isInxmailEnabled(): bool
    {
        if (empty($this->_data[self::CONFIG_FIELD_INXMAIL_ENABLED])) {
            $this->_data[self::CONFIG_FIELD_INXMAIL_ENABLED] = $this->_helper->getConfig(self::CONFIG_PATH_ENABLED);
        }

        return $this->_data[self::CONFIG_FIELD_INXMAIL_ENABLED];
    }

    /**
     * @return string
     */
    public function getRestApiUser(): string
    {
        if (empty($this->_data[self::CONFIG_FIELD_REST_USER])) {
            $this->_data[self::CONFIG_FIELD_REST_USER] = $this->_helper->getConfig(self::CONFIG_PATH_REST_USER);
        }

        return $this->_data[self::CONFIG_FIELD_REST_USER];
    }

    /**
     * @return string
     */
    public function getRestApiPassword(): string
    {
        if (empty($this->_data[self::CONFIG_FIELD_REST_PASSWORD])) {
            $this->_data[self::CONFIG_FIELD_REST_PASSWORD] = $this->_helper->getConfig(self::CONFIG_PATH_REST_PASSWORD);
        }

        return $this->_data[self::CONFIG_FIELD_REST_PASSWORD];
    }
}
