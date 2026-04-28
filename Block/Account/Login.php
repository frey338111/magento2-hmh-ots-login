<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Block\Account;

use Hmh\OtsLogin\Model\ResourceModel\ConfigProvider;
use Magento\Framework\View\Element\Template;

class Login extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getResendTimeoutSeconds(): int
    {
        return $this->configProvider->getResendTimeoutSeconds((int) $this->_storeManager->getStore()->getId());
    }
}
