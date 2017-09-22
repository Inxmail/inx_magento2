<?php

namespace Flagbit\Inxmail\Model\Config;

use \Flagbit\Inxmail\Helper\Config;
/**
 * Class SystemConfig
 * @package Flagbit\Inxmail\Model\Config
 */
class SystemConfig
{
    /**
     *
     */
    const CONFIG_PATH_URL = 'inxmail/general/api_url';
    /**
     *
     */
    const CONFIG_PATH_API_USER = 'inxmail/general/api_user';
    /**
     *
     */
    const CONFIG_PATH_API_KEY = 'inxmail/general/api_password';
    /**
     *
     */
    const CONFIG_PATH_API_LIST = 'inxmail/general/api_listid';

    /**
     *
     */
    const CONFIG_FIELD_URL = 'apiUrl';
    /**
     *
     */
    const CONFIG_FIELD_USER = 'apiUser';
    /**
     *
     */
    const CONFIG_FIELD_KEY = 'apiKey';
    /**
     *
     */
    const CONFIG_FIELD_LIST = 'apiList';

    /** @var SystemConfig */
    protected static $_config;
    /** @var Config|null */
    protected $_helper;

    /** @var array */
    protected $_data = array();

    /**
     * SystemConfig constructor.
     * @param Config $helper
     * @param string|null $apiUrl
     * @param string|null $apiUser
     * @param string|null $apiKey
     * @param string|null $apiList
     * @param string|null $apiList
     */
    protected function __construct(Config $helper, string $apiUrl = null, string $apiUser = null, string $apiKey = null, string $apiList = null)
    {
        $this->_helper = $helper;
        $this->_data[self::CONFIG_FIELD_URL] = $apiUrl;
        $this->_data[self::CONFIG_FIELD_USER] = $apiUser;
        $this->_data[self::CONFIG_FIELD_KEY] = $apiKey;
        $this->_data[self::CONFIG_FIELD_LIST] = $apiList;
    }

    /**
     * @param Config $helper
     * @param string|null $apiUrl
     * @param string|null $apiUser
     * @param string|null $apiKey
     * @param string|null $apiList
     * @return SystemConfig
     */
    public static function getSystemConfig(
        Config $helper, string $apiUrl = null, string $apiUser = null, string $apiKey = null, string $apiList = null): SystemConfig
    {
        if (self::$_config === null) {
            self::$_config = new self($helper, $apiUrl, $apiUser, $apiKey, $apiList);
        }

        return self::$_config;
    }

    /**
     * @param bool $refresh
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
}
