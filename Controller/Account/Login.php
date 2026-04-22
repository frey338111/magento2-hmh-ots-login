<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Controller\Account;

use Hmh\OtsLogin\Model\ResourceModel\ConfigProvider;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Login implements HttpGetActionInterface
{
    public function __construct(
        private readonly PageFactory $pageFactory,
        private readonly RedirectFactory $redirectFactory,
        private readonly ConfigProvider $configProvider
    ) {
    }

    public function execute(): ResultInterface
    {
        if (!$this->configProvider->isEnabled()) {
            $resultRedirect = $this->redirectFactory->create();
            $resultRedirect->setPath('customer/account/login');

            return $resultRedirect;
        }

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set(__('Login with One Time Code'));

        return $page;
    }
}
