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

use Flagbit\Inxmail\Logger\Logger;
use Flagbit\Inxmail\Model\Config\SystemConfig;
use Flagbit\Inxmail\Model\Request;
use Flagbit\Inxmail\Model\Request\RequestSubscriptionRecipients;
use Magento\Framework\View\Element\Context;

class AttribSelectInx extends MapSelect
{
    /**
     * @var \Flagbit\Inxmail\Model\Request\RequestRecipientAttributes
     */
    private $request;

    /**
     * @var SystemConfig
     */
    private $systemConfig;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * AttribSelectInx constructor
     *
     * @param Context $context
     * @param Request $request
     * @param SystemConfig $systemConfig
     * @param Logger $logger
     * @param array $data
     */
    public function __construct(
        Context $context,
        Request $request,
        SystemConfig $systemConfig,
        Logger $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->request = $request->getAttributesClient();
        $this->systemConfig = $systemConfig;
        $this->logger = $logger;
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

        try {
            /** @var array $attributes */
            $attributes = $this->request->sendRequest();
            $attributes = $attributes['_embedded']['inx:attributes'];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $attributes = $this->getAlreadySavedAttributeMapping();
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

    /**
     * @return array
     */
    private function getAlreadySavedAttributeMapping()
    {
        $alreadySavedAttributeMapping = $this->systemConfig->getMapConfig();

        foreach ($alreadySavedAttributeMapping as $attribute) {
            if (isset($attribute['inxAttrib'])) {
                $attributes[]['name'] = $attribute['inxAttrib'];
            }
        }

        return $attributes ?? [];
    }
}
