<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model\Resolver;

use Hmh\OtsLogin\Model\Service\SendOtsCode as SendOtsCodeService;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SendQtsCode implements ResolverInterface
{
    public function __construct(
        private readonly SendOtsCodeService $sendOtsCodeService
    ) {
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        $email = trim((string)($args['email'] ?? ''));

        if ($email === '') {
            throw new GraphQlInputException(__('Specify the "email" value.'));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new GraphQlInputException(__('The email address has an invalid format.'));
        }

        return [
            'success' => true,
            'message' => $this->sendOtsCodeService->execute($email),
        ];
    }
}
