<?php
namespace Flagbit\Inxmail\Model\Request;

use \Magento\Framework\ObjectManager\ObjectManager;

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
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $class
     * @param array|null $params
     * @return RequestInterface
     */
    public function create(string $class, array $params = null): RequestInterface
    {
        return $this->objectManager->create($class, $params);
    }
}
