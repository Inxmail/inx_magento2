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

use \Flagbit\Inxmail\Helper\Config;
use \Flagbit\Inxmail\Model\Request;
use \Flagbit\Inxmail\Model\Request\RequestRecipientAttributes;
use \Flagbit\Inxmail\Model\Config\SystemConfig;
use \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use \Magento\Framework\App\Cache\TypeListInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Data\Collection\AbstractDb;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Framework\Model\Context;
use \Magento\Framework\Model\ResourceModel\AbstractResource;
use \Magento\Framework\Registry;
use \Magento\Framework\Serialize\Serializer\Json;

/**
 * Class AttributeMapping
 *
 * @package Flagbit\Inxmail\Model\Config
 */
class AttributeMapping extends ArraySerialized
{
    /** @var \Flagbit\Inxmail\Model\Request */
    private $request;
    /** @var \Magento\Framework\Message\ManagerInterface */
    private $messageManager;

    /** @var array */
    private static $dateAllowdTypes = [
        RequestRecipientAttributes::LIST_TYPE_DATE_AND_TIME => 1,
        RequestRecipientAttributes::LIST_TYPE_DATE_ONLY => 1
    ];
    /**
     * @var \Flagbit\Inxmail\Helper\Config
     */
    private $inxConfig;

    /**
     * AttributeMapping constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Flagbit\Inxmail\Model\Request $request
     * @param \Flagbit\Inxmail\Helper\Config $inxConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Request $request,
        Config $inxConfig,
        ManagerInterface $messageManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null, array $data = [],
        Json $serializer = null
    )
    {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data, $serializer);
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->inxConfig = $inxConfig;
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        // For value validations
        $values = $this->getValue();

        /** @var \Flagbit\Inxmail\Model\Request\RequestRecipientAttributes $attribclient */
        $attributeRequest = $this->request->getAttributesClient();
        $attributes = $attributeRequest->sendRequest();

        foreach ($values as $key => $value) {
            if (\is_array($value) && $value['magAttrib'] === 'lastOrderDate'
                && !$this->validateFieldType($value['inxAttrib'], $attributes['_embedded']['inx:attributes'])
            ) {
                $this->messageManager->addErrorMessage(__('Last order only can be mapped to an date or datetime field.'));
                unset($values[$key]);
                break;
            }
        }

        // Validations
        $this->setValue($values);

        return parent::beforeSave();
    }

    private function validateFieldType(string $attributeName, array $attributes): bool
    {
        foreach ($attributes as $value) {
            if ($value['name'] === $attributeName) {
                $isAllowed = \array_key_exists($value['type'], self::$dateAllowdTypes);
                if ($isAllowed) {
                    $this->saveAttributeType($value['type']);
                }
                return $isAllowed;
            }
        }

        return false;
    }

    /**
     * @param $attributeType
     */
    private function saveAttributeType($attributeType)
    {
            $attributeConfig = \json_decode(
                $this->inxConfig->getConfig(
                    SystemConfig::CONFIG_PATH_ATTRIBUTES
                ),
                true
            );
            $attributeConfig['lastOrderDate'] = $attributeType;
            $this->inxConfig->saveConfig(
                SystemConfig::CONFIG_PATH_ATTRIBUTES,
                    \json_encode($attributeConfig),
                0
                );
    }
}
