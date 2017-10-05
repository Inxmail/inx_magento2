<?php

namespace Flagbit\Inxmail\Model\Config;

use \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;

/**
 * Class AttributeMapping
 *
 * @package Flagbit\Inxmail\Model\Config
 */
class AttributeMapping extends ArraySerialized
{
    /**
     * @return $this
     */
    public function beforeSave()
    {
        // For value validations
        $exceptions = $this->getValue();
        // Validations
        $this->setValue($exceptions);

        return parent::beforeSave();
    }
}
