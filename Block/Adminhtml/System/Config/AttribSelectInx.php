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

use Flagbit\Inxmail\Helper\Config;
use Flagbit\Inxmail\Model\Config\SystemConfig;
use Flagbit\Inxmail\Model\Request;
use Flagbit\Inxmail\Model\Request\RequestSubscriptionRecipients;
use Magento\Framework\View\Element\Context;

/**
 * Class AttribSelectInx
 *
 * @package Flagbit\Inxmail\Block\Adminhtml\System\Config
 */
class AttribSelectInx extends MapSelect
{
    /** @var \Flagbit\Inxmail\Model\Request\RequestRecipientAttributes */
    private $request;
    /** @var bool */
    private $isEnabled;

    /**
     * AttribSelectInx constructor
     *
     * @param Context $context
     * @param Request $request
     * @param array $data
     */
    public function __construct(
        Context $context,
        Request $request,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->request = $request->getAttributesClient();
        $this->isEnabled = SystemConfig::getSystemConfig($config)->isEnabled();
    }

    /**
     * Parse to html
     *
     * @return string
     */
    public function _toHtml(): string
    {
        $defAttributes = RequestSubscriptionRecipients::getStandardAttributes();
        $defAttributes = array_keys($defAttributes);

        if ($this->isEnabled) {
            try {
                /** @var array $attributes */
                $attributes = $this->request->sendRequest();
                $attributes = $attributes['_embedded']['inx:attributes'];
            } catch (\Exception $e) {
                $attributes = [];
            }
        } else {
            $attributes = [];
        }

        if (!$this->getOptions()) {
            foreach ($attributes as $key => $value) {
                if (!in_array($value['name'], $defAttributes, true)) {
                    $this->addOption($value['name'], $value['name']);
                }
            }
        }
        return parent::_toHtml();
    }
}
