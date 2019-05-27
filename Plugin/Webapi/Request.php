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

namespace Flagbit\Inxmail\Plugin\Webapi;

use \Magento\Framework\Interception\InterceptorInterface;
/**
 * Class Request
 * @package Flagbit\Inxmail\Plugin\Webapi
 */
class Request
{

    /**
     * @param \Magento\Framework\Interception\InterceptorInterface $rest
     * @param callable $proceed
     * @param array ...$args
     *
     * @return string
     */
    public function aroundGetHeader(InterceptorInterface $rest, callable $proceed, ...$args): string
    {
        $path = $rest->getUri()->getPath();
        if(strpos($path, 'inxmail') !== false) {
            $headers = $rest->getHeaders();
            if ($headers->get('accept')) {
                return $headers->get('accept')->getFieldValue();
            }
            return 'application/xml';
        }

        return $proceed($args[0] ?? '', $args[1] ?? false);
    }
}
