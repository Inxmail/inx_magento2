<?php

namespace Flagbit\Inxmail\Helper;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\RequestFactory;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Webapi\Controller\Rest\Router\Route;
use Magento\Webapi\Model\Rest\Config as RestApiConfig;

class ServiceHelper
{
    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var RestApiConfig
     */
    private $restApiConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @param RequestFactory         $requestFactory
     * @param RestApiConfig          $restApiConfig
     * @param StoreManagerInterface  $storeManager
     * @param \Magento\Framework\Webapi\ServiceOutputProcessor $serviceOutputProcessor
     */
    public function __construct
    (
        RequestFactory $requestFactory,
        RestApiConfig $restApiConfig,
        StoreManagerInterface $storeManager,
        ServiceOutputProcessor $serviceOutputProcessor
    )
    {
        $this->requestFactory = $requestFactory;
        $this->restApiConfig = $restApiConfig;
        $this->storeManager = $storeManager;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
    }

    /**
     * @param Route $route
     * @param mixed $outputData
     *
     * @return array
     */
    public function processOutput(Route $route, $outputData): array
    {
        return $this->serviceOutputProcessor->process(
            $outputData,
            $route->getServiceClass(),
            $route->getServiceMethod()
        );
    }

    /**
     * @param array $resource
     *
     * @return \Magento\Webapi\Controller\Rest\Router\Route
     * @throws \Exception
     */
    public function getRoute(array $resource): Route
    {
        $baseUrl = rtrim($this->storeManager->getStore()->getBaseUrl(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $route = null;

        if (array_key_exists('route', $resource)) {
            /** @var Request $request */
            $request = $this->requestFactory->create();

            $uri = $baseUrl . ltrim($resource['route'], DIRECTORY_SEPARATOR);
            $request->setBaseUrl($baseUrl);
            $request->setUri($uri);
            $request->setRequestUri($uri);
            $request->setMethod($resource['method']);
            $request->setPathInfo(null);

            /** @var Route[] $routes */
            $routes = $this->restApiConfig->getRestRoutes($request);
            $matched = [];
            foreach ($routes as $route) {
                $params = $route->match($request);
                if ($params !== false) {
                    $request->setParams($params);
                    $matched[] = $route;
                }
            }
            if (!empty($matched)) {
                $matched = array_pop($matched);
            }
            $route = $matched;
        }

        if (!$route instanceof Route) {
            throw new \Exception('Route could not be found. Please check your configuration');
        }

        return $route;
    }
}
