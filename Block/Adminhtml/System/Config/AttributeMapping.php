<?php

namespace Flagbit\Inxmail\Block\Adminhtml\System\Config;

use \Magento\Framework\DataObject;
use \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

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

    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn('magAttrib', ['label' => __('Magento Attribute'), 'renderer' => $this->getAttribSelectMag()]);
        $this->addColumn('inxAttrib', ['label' => __('Inxmail Attribute'), 'renderer' => $this->getAttribSelectInx()]);

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
