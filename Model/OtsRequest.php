<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model;

use Hmh\OtsLogin\Api\Data\OtsRequestExtensionInterface;
use Hmh\OtsLogin\Api\Data\OtsRequestInterface;
use Hmh\OtsLogin\Model\ResourceModel\OtsRequest as OtsRequestResource;
use Magento\Framework\Model\AbstractExtensibleModel;

class OtsRequest extends AbstractExtensibleModel implements OtsRequestInterface
{
    protected function _construct(): void
    {
        $this->_init(OtsRequestResource::class);
    }

    public function getEntityId(): ?int
    {
        $value = $this->getData(self::ENTITY_ID);

        return $value === null ? null : (int) $value;
    }

    public function setEntityId($entityId): OtsRequestInterface
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getCustomerId(): ?int
    {
        $value = $this->getData(self::CUSTOMER_ID);

        return $value === null ? null : (int) $value;
    }

    public function setCustomerId(int $customerId): OtsRequestInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    public function getWebsiteId(): ?int
    {
        $value = $this->getData(self::WEBSITE_ID);

        return $value === null ? null : (int) $value;
    }

    public function setWebsiteId(int $websiteId): OtsRequestInterface
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt(?string $createdAt): OtsRequestInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getCode(): ?string
    {
        return $this->getData(self::CODE);
    }

    public function setCode(string $code): OtsRequestInterface
    {
        return $this->setData(self::CODE, $code);
    }

    public function getStatus(): bool
    {
        return (bool) $this->getData(self::STATUS);
    }

    public function setStatus(bool $status): OtsRequestInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getExtensionAttributes(): ?OtsRequestExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(OtsRequestExtensionInterface $extensionAttributes): OtsRequestInterface
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
