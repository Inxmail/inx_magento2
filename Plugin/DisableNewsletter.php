<?php
namespace Flagbit\Inxmail\Plugin;

use \Magento\Customer\Block\Form\Register;

class DisableNewsletter {
    /**
     * @see \Magento\Customer\Block\Form\Register isNewsletterEnabled()
     * @param \Magento\Customer\Block\Form\Register $register
     * @param \Closure $proceed
     * @return boolean
     */
    public function aroundIsNewsletterEnabled(Register $register, \Closure $proceed): bool
    {
        // ToDo: question system-config for enabled inxmail && newsletter magento
        // use before instead of around
        return true;
    }
}
