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

namespace Flagbit\Inxmail\Block\Adminhtml\System\Config;

use \Magento\Framework\View\Element\Html\Select;

/**
 * Class MapSelect
 *
 * @package Flagbit\Inxmail\Block\Adminhtml\System\Config
 * @method void setName(string $value)
 */
abstract class MapSelect extends Select
{
    /**
     * @param string $value
     *
     * @return \Flagbit\Inxmail\Block\Adminhtml\System\Config\MapSelect
     */
    public function setInputName($value): MapSelect
    {
        return $this->setName($value);
    }

    /**
     * Parse to html
     *
     * @return string
     */
    public function _toHtml(): string
    {
        return parent::_toHtml();
    }
}
