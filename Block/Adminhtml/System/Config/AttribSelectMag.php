<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @author Flagbit GmbH
 * @copyright Copyright © 2017-2025 Inxmail GmbH
 * @license Licensed under the Open Software License version 3.0 (https://opensource.org/licenses/OSL-3.0)
 *
 */

namespace Flagbit\Inxmail\Block\Adminhtml\System\Config;

use Flagbit\Inxmail\Model\Request\RequestSubscriptionRecipients;

class AttribSelectMag extends MapSelect
{
    /**
     * Parse to html
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $attributes = RequestSubscriptionRecipients::getMapableAttributes();
            foreach ($attributes as $inxmail => $magento) {
                if ($magento === 'email') {
                    continue;
                }
                $this->addOption($magento, $magento);
            }
        }

        return parent::_toHtml();
    }
}
