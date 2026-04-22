<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CommunicationMethods implements OptionSourceInterface
{
    /**
     * @param array<string, string> $methods
     */
    public function __construct(
        private readonly array $methods = ['email' => 'Email']
    ) {
    }

    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->methods as $value => $label) {
            $options[] = [
                'value' => (string) $value,
                'label' => __((string) $label),
            ];
        }

        return $options;
    }
}
