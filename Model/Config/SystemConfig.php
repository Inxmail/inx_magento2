<?php

namespace Flagbit\Inxmail\Model\Config;

use \Flagbit\Inxmail\Helper\Config;
/**
 * Class SystemConfig
 *
 * @package Flagbit\Inxmail\Model\Config
 */
class SystemConfig
{
    /**
     * Contains the path of the inxmail configuration for the api url
     */
    const CONFIG_PATH_URL = 'inxmail/general/api_url';
    /**
     * Contains the path of the inxmail configuration for the api user
     */
    const CONFIG_PATH_API_USER = 'inxmail/general/api_user';
    /**
     * Contains the path of the inxmail configuration for the api secret
     */
    const CONFIG_PATH_API_KEY = 'inxmail/general/api_password';
    /**
     * Contains the path of the inxmail configuration for the list to sync to
     */
    const CONFIG_PATH_API_LIST = 'inxmail/general/api_listid';

    /**
     * Datafield key
     */
    const CONFIG_FIELD_URL = 'apiUrl';
    /**
     * Datafield key
     */
    const CONFIG_FIELD_USER = 'apiUser';
    /**
     * Datafield key
     */
    const CONFIG_FIELD_KEY = 'apiKey';
    /**
     * Datafield key
     */
    const CONFIG_FIELD_LIST = 'apiList';

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

    /**
     * @return bool
     */
    public function getInxDebug(): bool
    {

        if (empty($this->_data[self::CONFIG_FIELD_LIST])) {
            $this->_data[self::CONFIG_FIELD_LIST] = $this->_helper->getConfig('inxmail/general/debug');
        }

        return (bool)$this->_data[self::CONFIG_FIELD_LIST];
    }
}
