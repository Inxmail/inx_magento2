<?php
namespace Flagbit\Inxmail\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

class SubscriberSync extends AbstractHelper
{
    /**
     * Config constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ){
        $this->context = $context;
    }


}
