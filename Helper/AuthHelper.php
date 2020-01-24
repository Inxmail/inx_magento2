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

use Flagbit\Inxmail\Model\Config\SystemConfig;

/**
 * Class AuthHelper
 *
 * @package Flagbit\Inxmail\Helper
 */
class AuthHelper
{
    /** @var \Flagbit\Inxmail\Model\Config\SystemConfig */
    private $systemConfig;

    /**
     * AuthHelper constructor
     *
     * @param \Flagbit\Inxmail\Helper\Config $config
     */
    public function __construct(Config $config)
    {
        $this->systemConfig = SystemConfig::getSystemConfig($config);
    }


    /**
     * @param string $authString
     *
     * @return bool
     */
    public function checkAuth(string $authString): bool
    {
        $authenticated = false;

        if ($authString) {
            try {
                $authArray = explode(':',base64_decode(explode(' ', $authString)[1]));
            } catch (\Exception $e) {
                return $authenticated;
            }
            if (count($authArray) === 2) {
                $username = $this->systemConfig->getRestApiUser();
                $password = $this->systemConfig->getRestApiPassword();

                $authenticated = $username === $authArray[0] && $password === $authArray[1];
            }
        }

        return $authenticated;
    }
}
