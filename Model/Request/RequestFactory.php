<?php

namespace Flagbit\Inxmail\Model\Request;

use \Magento\Framework\ObjectManagerInterface;

/**
 * Class RequestFactory
 * @package Flagbit\Inxmail\Model\Request
 */
class RequestFactory
{
    /** @var \Magento\Framework\ObjectManager\ObjectManager */
    private $objectManager;

    /**
     * RequestFactory constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $class
     *
     * @return \Flagbit\Inxmail\Model\Request\RequestInterface
     */
    public function create(string $class): RequestInterface
    {
        return $this->objectManager->create($class);
    }
}
