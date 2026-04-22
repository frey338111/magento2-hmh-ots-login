<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface OtsRequestInterface extends ExtensibleDataInterface
{
    public const ENTITY_ID = 'entity_id';
    public const CUSTOMER_ID = 'customer_id';
    public const WEBSITE_ID = 'website_id';
    public const CREATED_AT = 'created_at';
    public const CODE = 'code';
    public const STATUS = 'status';

    public function getEntityId(): ?int;

    public function setEntityId($entityId): self;

    public function getCustomerId(): ?int;

    public function setCustomerId(int $customerId): self;

    public function getWebsiteId(): ?int;

    public function setWebsiteId(int $websiteId): self;

    public function getCreatedAt(): ?string;

    public function setCreatedAt(?string $createdAt): self;

    public function getCode(): ?string;

    public function setCode(string $code): self;

    public function getStatus(): bool;

    public function setStatus(bool $status): self;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Hmh\OtsLogin\Api\Data\OtsRequestExtensionInterface|null
     */
    public function getExtensionAttributes(): ?OtsRequestExtensionInterface;

    /**
     * Set an extension attributes object.
     *
     * @param \Hmh\OtsLogin\Api\Data\OtsRequestExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(OtsRequestExtensionInterface $extensionAttributes): self;
}
