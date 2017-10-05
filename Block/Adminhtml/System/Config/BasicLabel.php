<?php
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
