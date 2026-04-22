<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OtsRequest extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('hmh_ots_request', 'entity_id');
    }
}
