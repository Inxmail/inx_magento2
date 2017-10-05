<?php
namespace Flagbit\Inxmail\Plugin;

use \Flagbit\Inxmail\Helper\Config;
use \Flagbit\Inxmail\Model\Config\SystemConfig;
use \Magento\Customer\Block\Form\Register;

/**
 * Class DisableNewsletter
 *
 * @package Flagbit\Inxmail\Plugin
 */
class DisableNewsletter
{
    /**
     * @var \Flagbit\Inxmail\Model\Config\SystemConfig
     */
    private $systemConfig;

    /**
     * DisableNewsletter constructor.
     * @param \Flagbit\Inxmail\Helper\Config $config
     */
    public function __construct(Config $config)
    {
        $this->systemConfig = SystemConfig::getSystemConfig($config);
    }

    /**
     * @see \Magento\Customer\Block\Form\Register isNewsletterEnabled()
     *
     * @param \Magento\Customer\Block\Form\Register $register
     * @param \Closure $proceed
     *
     * @return boolean
     */
    public function aroundIsNewsletterEnabled(Register $register, \Closure $proceed): boolean
    {
        if ($this->systemConfig->isInxmailEnabled()) {
            $proceed();
            return true;
        }
        $proceed();
    }
}
