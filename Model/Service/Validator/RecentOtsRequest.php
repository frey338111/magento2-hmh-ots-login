<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model\Service\Validator;

use Hmh\OtsLogin\Model\ResourceModel\OtsRequest\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class RecentOtsRequest
{
    private const REQUEST_COOLDOWN_SECONDS = 300;

    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly DateTime $dateTime
    ) {
    }

    public function isValid(int $customerId, int $websiteId): bool
    {
        $threshold = $this->dateTime->gmtDate(
            'Y-m-d H:i:s',
            $this->dateTime->gmtTimestamp() - self::REQUEST_COOLDOWN_SECONDS
        );

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->addFieldToFilter('website_id', $websiteId);
        $collection->addFieldToFilter('created_at', ['gteq' => $threshold]);
        $collection->addFieldToFilter('status', ['eq' => 0]);
        $collection->setPageSize(1);

        return (int) $collection->getSize() === 0;
    }
}
