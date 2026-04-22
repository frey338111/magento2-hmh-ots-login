<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model\Service\Communication;

use Magento\Framework\Exception\LocalizedException;

interface CommunicationInterface
{
    /**
     * @throws LocalizedException
     */
    public function execute(string $recipientEmail, string $recipientName, string $passcode): void;
}
