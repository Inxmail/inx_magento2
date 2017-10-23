<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @copyright Copyright (c) 2017 Flagbit GmbH
 */

namespace Flagbit\Inxmail\Helper;

use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Config
 *
 * @package Flagbit\Inxmail\Helper
 */
class Config extends AbstractHelper
{
    /** @var string */
    private static $scope = ScopeInterface::SCOPE_STORE;

    /**
     * @param string $config
     *
     * @return string
     */
    public function getConfig(string $config = ''): string
    {
        if ($config !== '') {
            return ($this->scopeConfig->getValue($config, self::$scope)) ?? '';
        }
        return '';
    }
}
