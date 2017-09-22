<?php

namespace Flagbit\Inxmail\Helper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Helper\AbstractHelper;

class Config extends AbstractHelper{


    protected $_scope = ScopeInterface::SCOPE_STORE;

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function getConfig($config = null) {
        if ($config !== null) {
            return $this->scopeConfig->getValue($config, $this->_scope);
        }
        return false;
    }
}
