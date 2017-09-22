<?php
namespace Flagbit\Inxmail\Block\Adminhtml\System\Config;

use \Magento\Framework\Data\Form\Element\AbstractElement;
use \Flagbit\Inxmail\Helper\Version;
use \Magento\Backend\Block\Template\Context;

class ModuleVersion extends BasicLabel
{
    protected $_helper;

    public function __construct(Context $context, Version $helper)
    {
        $this->_helper = $helper;
        parent::__construct($context);
    }

    public function _getElementHtml(AbstractElement $element): string
    {
               return $this->_helper->getVersion();
    }
}
