<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model\Service;

use Hmh\OtsLogin\Api\OtsRequestRepositoryInterface;
use Hmh\OtsLogin\Model\ResourceModel\ConfigProvider;
use Hmh\OtsLogin\Model\ResourceModel\OtsRequest\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

class LoginWithOts
{
    private const PASSCODE_PATTERN = '/^[A-Za-z0-9]+$/';

    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly CollectionFactory $collectionFactory,
        private readonly OtsRequestRepositoryInterface $otsRequestRepository,
        private readonly CustomerSession $customerSession,
        private readonly ConfigProvider $configProvider,
        private readonly DateTime $dateTime
    ) {
    }

    public function execute(string $email, string $passcode): void
    {
        if (!preg_match(self::PASSCODE_PATTERN, $passcode)) {
            throw new GraphQlInputException(
                __('The one time code must contain only letters and numbers.')
            );
        }

        $websiteId = (int) $this->storeManager->getStore()->getWebsiteId();

        try {
            $customer = $this->customerRepository->get($email, $websiteId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlInputException(
                __('There is no customer registered with that email address.'),
                $exception
            );
        }

        $validPeriodInMinutes = $this->configProvider->getPasscodeValidPeriod((int) $this->storeManager->getStore()->getId());
        $validFrom = $this->dateTime->gmtDate(
            'Y-m-d H:i:s',
            $this->dateTime->gmtTimestamp() - ($validPeriodInMinutes * 60)
        );

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('customer_id', (int) $customer->getId());
        $collection->addFieldToFilter('website_id', $websiteId);
        $collection->addFieldToFilter('code', $passcode);
        $collection->addFieldToFilter('status', 0);
        $collection->addFieldToFilter('created_at', ['gteq' => $validFrom]);
        $collection->setPageSize(1);

        $otsRequest = $collection->getFirstItem();

        if (!$otsRequest->getId()) {
            throw new GraphQlInputException(__('The one time code is invalid or has already been used.'));
        }

        $otsRequest->setStatus(true);
        $this->otsRequestRepository->save($otsRequest);
        $this->customerSession->setCustomerDataAsLoggedIn($customer);
    }
}
