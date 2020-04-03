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
use Flagbit\Inxmail\Logger\Logger;
use Flagbit\Inxmail\Model\Config\SystemConfig;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

/**
 * Class AttributeMapping
 *
 * @package Flagbit\Inxmail\Block\Adminhtml\System\Config
 */
class AttributeMapping extends AbstractFieldArray
{
    /**
     * @var \Flagbit\Inxmail\Block\Adminhtml\System\Config\AttribSelectInx
     */
    private $mapInx;

    /**
     * @var \Flagbit\Inxmail\Block\Adminhtml\System\Config\AttribSelectMag
     */
    private $mapMag;

    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @var \Magento\Framework\View\LayoutInterface|null
     */
    private $layout;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        Context $context,
        Config $config,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->isEnabled = SystemConfig::getSystemConfig($config)->isEnabled();
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $attributeSelectMagento = $this->getAttribSelectMag();
        $attributeSelectInxmail = $this->getAttribSelectInx();

        if (null !== $attributeSelectMagento && null !== $attributeSelectInxmail) {
            $this->addColumn('magAttrib', ['label' => __('Magento customer attribute'), 'renderer' => $attributeSelectMagento]);
            $this->addColumn('inxAttrib', ['label' => __('Inxmail Professional recipient attribute'), 'renderer' => $attributeSelectInxmail]);
        }

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Get mapping options select
     *
     * @return \Flagbit\Inxmail\Block\Adminhtml\System\Config\AttribSelectMag
     */
    private function getAttribSelectMag(): AttribSelectMag
    {
        if (null === $this->mapMag) {
            $layout = $this->getInitializedLayout();
            if (null !== $layout) {
                $this->mapMag = $layout->createBlock(
                    AttribSelectMag::class,
                    '',
                    ['data' => ['is_render_to_js_template' => true]]
                );
            }
        }

        return $this->mapMag;
    }

    /**
     * @return \Magento\Framework\View\LayoutInterface|null
     */
    private function getInitializedLayout()
    {
        if (null === $this->layout) {
            try {
                $this->layout = $this->getLayout();
            } catch (\Magento\Framework\Exception\LocalizedException $localizedException) {
                $this->logger->error(
                    $localizedException->getMessage(),
                    ['trace' => $localizedException->getTraceAsString()]
                );
            }
        }

        return $this->layout;
    }

    /**
     * Get mapping options select
     *
     * @return \Flagbit\Inxmail\Block\Adminhtml\System\Config\AttribSelectInx
     */
    private function getAttribSelectInx(): AttribSelectInx
    {
        if (null === $this->mapInx) {
            $layout = $this->getInitializedLayout();
            if (null !== $layout) {
                $this->mapInx = $layout->createBlock(
                    AttribSelectInx::class,
                    '',
                    ['data' => ['is_render_to_js_template' => true]]
                );
            }
        }

        return $this->mapInx;
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
