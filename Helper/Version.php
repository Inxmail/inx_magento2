<?php

namespace Flagbit\Inxmail\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleListInterface;
use \Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Version
 *
 * @package Flagbit\Inxmail\Helper
 */
class Version extends AbstractHelper
{
    /** @var string */
    const MODULE_NAME = 'Flagbit_Inxmail';

    /** @var \Magento\Framework\Module\ModuleListInterface */
    protected $_moduleList;

    /**
     * Version constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(Context $context, ModuleListInterface $moduleList)
    {
        $this->_moduleList = $moduleList;
        parent::__construct($context);
    }

    /**
     * Get module version from config
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->_moduleList->getOne(self::MODULE_NAME)['setup_version'];
    }
}
