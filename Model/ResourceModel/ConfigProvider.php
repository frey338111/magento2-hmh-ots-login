<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model\ResourceModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    public const XML_PATH_ENABLE = 'hmh_otslogin/general/enable';
    public const XML_PATH_PASSCODE_VALID_PERIOD = 'hmh_otslogin/general/passcode_valid_period';
    public const XML_PATH_COMMUNICATION_METHODS = 'hmh_otslogin/general/communication_methods';
    public const XML_PATH_RESEND_TIMEOUT_SECONDS = 'hmh_otslogin/general/resend_timeout_seconds';
    public const XML_PATH_EMAIL_SENDER = 'hmh_otslogin/email/sender';
    private const DEFAULT_RESEND_TIMEOUT_SECONDS = 300;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getPasscodeValidPeriod(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_PASSCODE_VALID_PERIOD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCommunicationMethods(?int $storeId = null): array
    {
        $value = (string) $this->scopeConfig->getValue(
            self::XML_PATH_COMMUNICATION_METHODS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === '') {
            return ['email'];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    public function getResendTimeoutSeconds(?int $storeId = null): int
    {
        $value = (int) $this->scopeConfig->getValue(
            self::XML_PATH_RESEND_TIMEOUT_SECONDS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $value > 0 ? $value : self::DEFAULT_RESEND_TIMEOUT_SECONDS;
    }

    public function getEmailSender(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
