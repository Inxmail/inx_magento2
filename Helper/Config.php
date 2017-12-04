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
use \Magento\Framework\Encryption\EncryptorInterface;
use \Magento\Framework\App\Helper\Context;

/**
 * Class Config
 *
 * @package Flagbit\Inxmail\Helper
 */
class Config extends AbstractHelper
{
    /** @var string */
    private static $scope = ScopeInterface::SCOPE_STORE;

    /** @var \Magento\Framework\Encryption\EncryptorInterface */
    protected $_enc;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Encryption\EncryptorInterface $enc
     */
    public function __construct(Context $context, EncryptorInterface $enc)
    {
        parent::__construct($context);
        $this->_enc = $enc;
    }

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

    public function getEncryptor(): EncryptorInterface
    {
        return $this->_enc;
    }
}
