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

use Flagbit\Inxmail\Helper\Config as ConfigHelper;
use Flagbit\Inxmail\Model\Request;
use Flagbit\Inxmail\Model\Request\RequestRecipientAttributes;
use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class AttributeMapping
 *
 * @package Flagbit\Inxmail\Model\Config
 */
class AttributeMapping extends ArraySerialized
{
    /**
     * @var array
     */
    private static $dateAllowdTypes = [
        RequestRecipientAttributes::LIST_TYPE_DATE_AND_TIME => 1,
        RequestRecipientAttributes::LIST_TYPE_DATE_ONLY => 1
    ];
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var ConfigHelper
     */
    private $inxConfig;
    /**
     * @var SystemConfig
     */
    private $inxSystemConfig;

    /**
     * AttributeMapping constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param Request $request
     * @param ConfigHelper $inxConfig
     * @param SystemConfig $inxSystemConfig
     * @param ManagerInterface $messageManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Request $request,
        ConfigHelper $inxConfig,
        SystemConfig $inxSystemConfig,
        ManagerInterface $messageManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null, array $data = [],
        Json $serializer = null
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data, $serializer);
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->inxConfig = $inxConfig;
        $this->inxSystemConfig = $inxSystemConfig;
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        // For value validations
        parent::beforeSave();
        $values = $this->getValue();

        if (is_array($values)) {
            if (!empty($values)) {
                /** @var RequestRecipientAttributes $attribclient */
                $attributeRequest = $this->request->getAttributesClient();
                $attributes = $attributeRequest->sendRequest();
            }

            foreach ($values as $key => $value) {
                if (\is_array($value) && $value['magAttrib'] === 'lastOrderDate'
                    && !$this->validateFieldType($value['inxAttrib'], $attributes['_embedded']['inx:attributes'])
                ) {
                    $this->messageManager->addErrorMessage(__('Last order only can be mapped to an date or datetime field.'));
                    unset($values[$key]);
                    break;
                }
            }
        }

        // Validations
        $this->setValue($values);

        return $this;
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
    private function saveAttributeType($attributeType): void
    {
        $attributeConfig = \json_decode(
            $this->inxConfig->getConfig(
                $this->inxSystemConfig->getAttributesConfigPath()
            ),
            true
        );
        $attributeConfig['lastOrderDate'] = $attributeType;
        $this->inxConfig->saveConfig(
            $this->inxSystemConfig->getAttributesConfigPath(),
            \json_encode($attributeConfig),
            0
        );
    }
}
