<?php

namespace Flagbit\Inxmail\Helper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Config
 * @package Flagbit\Inxmail\Helper
 */
class Config extends AbstractHelper{


    /**
     * @var string
     */
    private static $_scope = ScopeInterface::SCOPE_STORE;

    /**
     * Config constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @param string $config
     * @return string
     */
    public function getConfig(string $config = ''): string
    {
        if ($config !== '') {
            return $this->scopeConfig->getValue($config, self::$_scope);
        }
        return '';
    }
}
