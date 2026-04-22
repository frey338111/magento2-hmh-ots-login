<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model\ResourceModel\OtsRequest;

use Hmh\OtsLogin\Model\OtsRequest as OtsRequestModel;
use Hmh\OtsLogin\Model\ResourceModel\OtsRequest as OtsRequestResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(OtsRequestModel::class, OtsRequestResource::class);
    }
}
