<?php
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
