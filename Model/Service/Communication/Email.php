<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model\Service\Communication;

use Hmh\OtsLogin\Model\ResourceModel\ConfigProvider;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;

class Email implements CommunicationInterface
{
    public const DEFAULT_EMAIL_TEMPLATE = 'hmh_otslogin_email_template';

    public function __construct(
        private readonly TransportBuilder $transportBuilder,
        private readonly StateInterface $inlineTranslation,
        private readonly StoreManagerInterface $storeManager,
        private readonly ConfigProvider $configProvider
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function execute(string $recipientEmail, string $recipientName, string $passcode): void
    {
        $store = $this->storeManager->getStore();
        $storeId = (int) $store->getId();
        $sender = $this->configProvider->getEmailSender($storeId) ?: 'general';

        $this->inlineTranslation->suspend();

        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier(self::DEFAULT_EMAIL_TEMPLATE)
                ->setTemplateOptions([
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars([
                    'customer_name' => $recipientName,
                    'passcode' => $passcode,
                    'store' => $store,
                ])
                ->setFromByScope($sender, $storeId)
                ->addTo($recipientEmail, $recipientName)
                ->getTransport();

            $transport->sendMessage();
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
