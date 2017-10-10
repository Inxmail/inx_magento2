<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @copyright Copyright (c) 2017 Flagbit GmbH
 */

namespace Flagbit\Inxmail\Block\Adminhtml\System\Config;

use \Magento\Framework\View\Element\Context;
use \Flagbit\Inxmail\Model\Request;
use \Flagbit\Inxmail\Model\Request\RequestSubscriptionRecipients;


/**
 * Class AttribSelectInx
 *
 * @package Flagbit\Inxmail\Block\Adminhtml\System\Config
 */
class AttribSelectInx extends MapSelect
{
    /** @var \Flagbit\Inxmail\Model\Request\RequestRecipientAttributes */
    private $request;

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
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->request = $request->getAttributesClient();
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

        /** @var array $attributes */
        $attributes = $this->request->sendRequest();
        $attributes = $attributes['_embedded']['inx:attributes'];

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
