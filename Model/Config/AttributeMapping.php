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
