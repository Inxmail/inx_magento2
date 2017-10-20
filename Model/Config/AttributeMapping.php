<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @copyright Copyright (c) 2017 Flagbit GmbH
 */

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
