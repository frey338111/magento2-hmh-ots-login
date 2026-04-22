<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model\Service\Util;

use Magento\Framework\Math\Random;

class GernateOtsCode
{
    private const CODE_LENGTH = 8;
    private const CODE_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function __construct(
        private readonly Random $random
    ) {
    }

    public function execute(): string
    {
        return $this->random->getRandomString(self::CODE_LENGTH, self::CODE_CHARS);
    }
}
