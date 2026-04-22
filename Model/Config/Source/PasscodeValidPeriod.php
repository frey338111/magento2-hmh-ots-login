<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PasscodeValidPeriod implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $result = [];
        for ($minutes = 5; $minutes <= 30; $minutes += 5) {
            $result[] = [
                'value' => $minutes,
                'label' => __("%1 mins", $minutes),
            ];
        }
        return $result;
    }
}
