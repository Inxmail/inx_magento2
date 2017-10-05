<?php

namespace Flagbit\Inxmail\Block\Adminhtml\System\Config;

use \Magento\Framework\Data\Form\Element\AbstractElement;
use \Flagbit\Inxmail\Helper\Version;
use \Magento\Backend\Block\Template\Context;

/**
 * Class ModuleVersion
 *
 * @package Flagbit\Inxmail\Block\Adminhtml\System\Config
 */
class ModuleVersion extends BasicLabel
{
    /** @var \Flagbit\Inxmail\Helper\Version */
    private $helper;

    /**
     * ModuleVersion constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Flagbit\Inxmail\Helper\Version $helper
     */
    public function __construct(Context $context, Version $helper)
    {
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function _getElementHtml(AbstractElement $element): string
    {
        return $this->helper->getVersion();
    }
}
