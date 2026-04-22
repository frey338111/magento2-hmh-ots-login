<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model\Service;

use Hmh\OtsLogin\Api\OtsRequestRepositoryInterface;
use Hmh\OtsLogin\Model\OtsRequestFactory;
use Hmh\OtsLogin\Model\ResourceModel\ConfigProvider;
use Hmh\OtsLogin\Model\Service\Communication\CommunicationInterface;
use Hmh\OtsLogin\Model\Service\Util\GernateOtsCode;
use Hmh\OtsLogin\Model\Service\Validator\RecentOtsRequest;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Store\Model\StoreManagerInterface;

class SendOtsCode
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly GernateOtsCode $gernateOtsCode,
        private readonly OtsRequestFactory $otsRequestFactory,
        private readonly OtsRequestRepositoryInterface $otsRequestRepository,
        private readonly ConfigProvider $configProvider,
        private readonly RecentOtsRequest $recentOtsRequestValidator,
        private readonly array $communicationMethods = []
    ) {
    }

    public function execute(string $email): string
    {
        $websiteId = (int) $this->storeManager->getStore()->getWebsiteId();

        try {
            $customer = $this->customerRepository->get($email, $websiteId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlInputException(
                __('There is no customer registered with that email address.'),
                $exception
            );
        }

        if (!$this->recentOtsRequestValidator->isValid((int) $customer->getId(), $websiteId)) {
            return (string) __('The email has been sent. Please be patient.');
        }

        /** @var \Hmh\OtsLogin\Model\OtsRequest $otsRequest */
        $otsRequest = $this->otsRequestFactory->create();
        $otsRequest->setCustomerId((int) $customer->getId());
        $otsRequest->setWebsiteId($websiteId);
        $otsRequest->setCode($this->gernateOtsCode->execute());
        $otsRequest->setStatus(false);

        $otsRequest = $this->otsRequestRepository->save($otsRequest);

        try {
            foreach ($this->getCommunicationMethods() as $communicationMethod) {
                $communicationMethod->execute(
                    $email,
                    trim((string) $customer->getFirstname() . ' ' . (string) $customer->getLastname()),
                    (string) $otsRequest->getCode()
                );
            }
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(
                __('The one time code could not be emailed right now. Please try again later.'),
                $exception
            );
        }

        return (string) __('If the email address exists, a one time code request has been created.');
    }

    /**
     * @throws LocalizedException
     */
    private function getCommunicationMethods(): array
    {
        $methods = [];
        foreach ($this->configProvider->getCommunicationMethods((int) $this->storeManager->getStore()->getId()) as $methodCode) {
            $communicationMethod = $this->communicationMethods[$methodCode] ?? null;

            if (!$communicationMethod instanceof CommunicationInterface) {
                throw new LocalizedException(
                    __('The "%1" communication method is not available.', $methodCode)
                );
            }

            $methods[] = $communicationMethod;
        }

        return $methods;
    }
}
