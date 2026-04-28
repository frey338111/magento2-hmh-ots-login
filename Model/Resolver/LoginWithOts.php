<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model\Resolver;

use Hmh\OtsLogin\Model\Resolver\Validator\OtsLoginInputValidator;
use Hmh\OtsLogin\Model\Service\LoginWithOts as LoginWithOtsService;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class LoginWithOts implements ResolverInterface
{
    public function __construct(
        private readonly LoginWithOtsService $loginWithOtsService,
        private readonly OtsLoginInputValidator $inputValidator
    ) {
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        $email = trim((string) ($args['email'] ?? ''));
        $passcode = trim((string) ($args['passcode'] ?? ''));
        $formKey = trim((string) ($args['formKey'] ?? ''));

        if (!$this->inputValidator->isValid(['email' => $email, 'passcode' => $passcode, 'formKey' => $formKey])) {
            $messages = $this->inputValidator->getMessages();
            throw new GraphQlInputException(current($messages) ?: __('Invalid request.'));
        }

        $this->loginWithOtsService->execute($email, $passcode);

        return [
            'success' => true,
            'message' => (string) __('One time code login succeeded.'),
        ];
    }
}
