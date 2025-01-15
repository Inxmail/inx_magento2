<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @author Flagbit GmbH
 * @copyright Copyright © 2017-2025 Inxmail GmbH
 * @license Licensed under the Open Software License version 3.0 (https://opensource.org/licenses/OSL-3.0)
 *
 */

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
     * @param array $args
     *
     * @return bool
     */
    public function aroundIsNewsletterEnabled(Register $register, \Closure $proceed, array ...$args): bool
    {
        if ($this->systemConfig->isEnabled()) {
            return true;
        }
        return $proceed(...array_values($args));
    }
}
