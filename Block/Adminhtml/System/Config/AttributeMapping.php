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

use \Magento\Framework\DataObject;
use \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use \Magento\Backend\Block\Template\Context;
use \Flagbit\Inxmail\Helper\Config;
use \Flagbit\Inxmail\Model\Config\SystemConfig;
/**
 * Class AttributeMapping
 *
 * @package Flagbit\Inxmail\Block\Adminhtml\System\Config
 */
class AttributeMapping extends AbstractFieldArray
{

    /** @var \Flagbit\Inxmail\Block\Adminhtml\System\Config\AttribSelectInx */
    private $mapInx;
    /** @var \Flagbit\Inxmail\Block\Adminhtml\System\Config\AttribSelectMag */
    private $mapMag;
    /** @var bool */
    private $isEnabled;

    public function __construct(Context $context, Config $config) {
        parent::__construct($context);
        $this->isEnabled = SystemConfig::getSystemConfig($config)->isInxmailEnabled();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn('magAttrib', ['label' => __('Magento customer attribute'), 'renderer' => $this->getAttribSelectMag()]);
        $this->addColumn('inxAttrib', ['label' => __('Inxmail Professional recipient attribute'), 'renderer' => $this->getAttribSelectInx()]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }


    /**
     * Get mapping options select
     *
     * @return \Flagbit\Inxmail\Block\Adminhtml\System\Config\AttribSelectInx
     */
    private function getAttribSelectInx(): AttribSelectInx
    {
        if (!$this->mapInx) {
            $this->mapInx = $this->getLayout()->createBlock(
                AttribSelectInx::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->mapInx;
    }

    /**
     * Get mapping options select
     *
     * @return \Flagbit\Inxmail\Block\Adminhtml\System\Config\AttribSelectMag
     */
    private function getAttribSelectMag(): AttribSelectMag
    {
        if (!$this->mapMag) {
            $this->mapMag = $this->getLayout()->createBlock(
                AttribSelectMag::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->mapMag;
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        if ($this->isEnabled || $row->getData('magAttrib')) {
            $options = [];
            $magAttribute = $row->getData('magAttrib');
            $inxAttribute = $row->getData('inxAttrib');

            $magKey = 'option_' . $this->getAttribSelectMag()->calcOptionHash($magAttribute);
            $options[$magKey] = 'selected="selected"';

            $inxKey = 'option_' . $this->getAttribSelectInx()->calcOptionHash($inxAttribute);
            $options[$inxKey] = 'selected="selected"';

            $row->setData('option_extra_attrs', $options);
        }
    }
}
