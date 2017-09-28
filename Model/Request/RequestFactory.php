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
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $class
     * @param array|null $params
     * @return RequestInterface
     */
    public function create(string $class): RequestInterface
    {
        try{
            return $this->objectManager->create($class);
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }
}
