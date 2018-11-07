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

namespace Flagbit\Inxmail\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Class DebugLog
 *
 * @package Flagbit\Inxmail\Logger\Handler
 */
class DebugLog extends Base
{
    /** @var string */
    protected $fileName = '/var/log/inxmail.log';
    /** @var int */
    protected $loggerType = Logger::DEBUG;
}
