<?php
namespace Flagbit\Inxmail\Controller\Rest;

use \Flagbit\Inxmail\Helper\ServiceHelper;
use \Flagbit\Inxmail\Helper\AuthHelper;
use \Magento\Framework\Webapi\Rest\Response as RestResponse;
use \Magento\Catalog\Model\ProductRepository;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Controller\Result\Raw;
use \Magento\Framework\App\RouterInterface;
use \Magento\Framework\App\Action\Action;

class Products extends Action
{

    const ROUTE_PATH = '/V1/products/:sku';
    const ROUTE_METHOD = \Zend_Http_Client::GET;

    /** @var \Flagbit\Inxmail\Helper\AuthHelper */
    private $authHelper;
    /** @var \Flagbit\Inxmail\Helper\ServiceHelper */
    private $serviceHelper;

    /** @var \Magento\Framework\App\Action\Context */
    private $context;
    /** @var \Magento\Framework\Controller\Result\Raw */
    private $rawResult;
    /** @var \Magento\Catalog\Model\ProductRepository */
    private $productRepostiory;
    /** @var \Magento\Framework\Webapi\Rest\Response */
    private $restResponse;


    /**
     * Products constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\Raw $rawResult
     * @param \Flagbit\Inxmail\Helper\ServiceHelper $serviceHelper
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\Webapi\Rest\Response $restResponse
     * @param \Flagbit\Inxmail\Helper\AuthHelper $authHelper
     */
    public function __construct(
        Context $context,
        Raw $rawResult,
        ServiceHelper $serviceHelper,
        ProductRepository $productRepository,
        RestResponse $restResponse,
        AuthHelper $authHelper
    ){
        parent::__construct($context);

        $this->context = $context;
        $this->rawResult = $rawResult;
        $this->serviceHelper = $serviceHelper;
        $this->productRepostiory = $productRepository;
        $this->restResponse = $restResponse;
        $this->authHelper = $authHelper;
    }


    /**
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\Webapi\Rest\Response
     */
    public function execute()
    {
        $auth = $this->context->getRequest()->getHeader('authorization');
        $authenticated = $this->authHelper->checkAuth($auth);

        if(!$authenticated){
            $this->rawResult->setHeader('WWW-Authenticate', 'Basic realm="RealmName"');
            $this->rawResult->setHttpResponseCode(200);
            return $this->rawResult;
        }

        $params = $this->context->getRequest()->getParams();

        if (count($params) === 0 || empty(array_keys($params)[0])) {
            $this->endError(400, 'No Sku');
            return $this->rawResult;
        }

        $sku =  array_keys($params)[0];

        /** @var \Magento\Framework\App\RouterInterface $route */
        $route = $this->getRoute();
        if (!$route) {
            return $this->rawResult;
        }

        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $this->getProductBySku($sku);
        if (!$product) {
            return $this->rawResult;
        }

        /** @var array $output */
        $output = $this->serviceHelper->processOutput($route, $product);
        $this->restResponse->setMimeType('application/xml');
        $this->restResponse->setHeader('Content-Type:', 'text/xml; charset=utf-8', true);
        $this->restResponse->prepareResponse($output);

        return $this->restResponse;
    }

    /**
     * @return \Magento\Framework\App\RouterInterface
     */
    private function getRoute(): RouterInterface
    {
        try {
            return $this->serviceHelper->getRoute(array('route' => self::ROUTE_PATH, 'method' => self::ROUTE_METHOD));
        } catch (\Exception $e) {
            $this->endError(500, __('Route or method not found'));
            return null;
        }
    }

    /**
     * @param string $sku
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed|null
     */
    private function getProductBySku(string $sku)
    {
        try {
            return $this->productRepostiory->get($sku);
        } catch (\Exception $e) {
            $this->endError(404, __('Requested product doesn\'t exist'));
            return null;
        }
    }

    /**
     * @param int $id
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed|null
     */
    private function getProductById(int $id){
        $product = null;

        try {
            $product = $this->productRepostiory->getById($id);
        } catch (\Exception $e) {
            $this->endError(404, __('Requested product doesn\'t exist'));
            return null;
        }

        return $product;
    }

    /**
     * @param int $status
     * @param string $message
     */
    private function endError(int $status, string $message)
    {
        $this->rawResult->setHttpResponseCode($status);
        $this->rawResult->setHeader('Content-Type', 'text/xml');
        $this->rawResult->setContents("<response><message>$message</message></response>");
    }
}
