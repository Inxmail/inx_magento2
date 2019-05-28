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

namespace Flagbit\Inxmail\Helper;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\Config\Storage\WriterInterface;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\Encryption\EncryptorInterface;
use \Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 *
 * @package Flagbit\Inxmail\Helper
 */
class Config extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /** @var string */
    private static $scope = ScopeInterface::SCOPE_STORE;

    /** @var \Magento\Framework\Encryption\EncryptorInterface */
    protected $_enc;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Encryption\EncryptorInterface $enc
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     */
    public function __construct(Context $context, EncryptorInterface $enc, WriterInterface $configWriter)
    {
        parent::__construct($context);
        $this->_enc = $enc;
        $this->configWriter = $configWriter;
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

    public function saveConfig(string $path, $value = null, $storeId = 0)
    {
        $this->configWriter->save(
            $path,
            $value,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $storeId
        );
    }
}
