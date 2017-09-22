<?php
namespace Flagbit\Inxmail\Model\Request;

use \Magento\Framework\ObjectManager\ObjectManager;

class RequestFactory
{
    /** @var \Magento\Framework\ObjectManager\ObjectManager */
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(string $class, array $params = null){
        return $this->objectManager->create($class, $params);
    }
}
