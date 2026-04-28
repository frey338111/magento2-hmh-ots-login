<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model\Resolver\Validator;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Validator\AbstractValidator;

class OtsLoginInputValidator extends AbstractValidator
{
    public function __construct(
        private readonly FormKey $formKey
    ) {
    }

    /**
     * @param array{email?: string, passcode?: string, formKey?: string} $value
     */
    public function isValid($value): bool
    {
        $this->_clearMessages();

        if (!is_array($value)) {
            $this->_addMessages([__('Invalid request.')]);
            return false;
        }

        $this->validateEmail((string)($value['email'] ?? ''));

        if (array_key_exists('passcode', $value)) {
            $this->validatePasscode((string)$value['passcode']);
        }

        $this->validateFormKey((string)($value['formKey'] ?? ''));

        return !$this->hasMessages();
    }

    private function validateEmail(string $email): void
    {
        if ($email === '') {
            $this->_addMessages([__('Specify the "email" value.')]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->_addMessages([__('The email address has an invalid format.')]);
        }
    }

    private function validatePasscode(string $passcode): void
    {
        if ($passcode === '') {
            $this->_addMessages([__('Specify the "passcode" value.')]);
            return;
        }

        if (!ctype_alnum($passcode)) {
            $this->_addMessages([__('The passcode has an invalid format.')]);
        }
    }

    private function validateFormKey(string $formKey): void
    {
        if ($formKey === '' || !ctype_alnum($formKey)) {
            $this->_addMessages([__('Invalid form key. Please refresh the page.')]);
            return;
        }

        if (!Security::compareStrings($formKey, $this->formKey->getFormKey())) {
            $this->_addMessages([__('Invalid form key. Please refresh the page.')]);
        }
    }
}
