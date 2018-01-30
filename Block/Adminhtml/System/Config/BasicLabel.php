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

use \Magento\Config\Block\System\Config\Form\Field;
use \Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class BasicLabel
 *
 * @package Flagbit\Inxmail\Block\Adminhtml\System\Config
 *
 * @method string _getElementHtml(AbstractElement $element)
 * @method string _decorateRowHtml(AbstractElement $element, $html)
 */
class BasicLabel extends Field
{
    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $html = '<td class="label">' . $element->getLabel() . '</td>';
        $html .= '<td class="value">' . $this->_getElementHtml($element) . '</td>';

        return $this->_decorateRowHtml($element, $html);
    }
}
