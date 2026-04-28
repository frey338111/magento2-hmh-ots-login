<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model\Resolver;

use Hmh\OtsLogin\Model\Resolver\Validator\OtsLoginInputValidator;
use Hmh\OtsLogin\Model\Service\SendOtsCode as SendOtsCodeService;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SendQtsCode implements ResolverInterface
{
    public function __construct(
        private readonly SendOtsCodeService $sendOtsCodeService,
        private readonly OtsLoginInputValidator $inputValidator
    ) {
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        $email = trim((string)($args['email'] ?? ''));
        $formKey = trim((string)($args['formKey'] ?? ''));

        if (!$this->inputValidator->isValid(['email' => $email, 'formKey' => $formKey])) {
            $messages = $this->inputValidator->getMessages();
            throw new GraphQlInputException(current($messages) ?: __('Invalid request.'));
        }

        return [
            'success' => true,
            'message' => $this->sendOtsCodeService->execute($email),
        ];
    }
}
