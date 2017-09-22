<?php
/**
 * Created by PhpStorm.
 * User: peter_lelewel
 * Date: 14.09.17
 * Time: 15:24
 */
namespace Flagbit\Inxmail\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleListInterface;
use \Magento\Framework\App\Helper\AbstractHelper;

class Version extends AbstractHelper
{
    const MODULE_NAME = 'Flagbit_Inxmail';

    protected $_moduleList;

    public function __construct(
        Context $context,

        ModuleListInterface $moduleList
    )
    {
        $this->_moduleList = $moduleList;
        parent::__construct($context);
    }

    public function getVersion()
    {
        return $this->_moduleList->getOne(self::MODULE_NAME)['setup_version'];
    }
}
