define([
    'jquery',
    'mage/validation'
], function ($) {
    'use strict';

    const EMAIL_VALIDATE = '{required:true, \'validate-email\':true}';
    const PASSCODE_VALIDATE = '{required:true}';
    const DEFAULT_RESEND_TIMEOUT_SECONDS = 300;

    return function (config, element) {
        const container = element;
        const messages = document.getElementById('ots-request-messages');
        const actionsToolbar = document.getElementById('ots-actions-toolbar');
        const submitButton = document.getElementById('send-ots-code');
        const submitButtonWrapper = submitButton.parentElement;
        const submitLabel = document.getElementById('ots-submit-label');
        const resendCodeAction = document.getElementById('ots-resend-code-action');
        const resendCodeButton = document.getElementById('resend-ots-code');
        const resendCountdown = document.getElementById('ots-resend-countdown');
        const note = document.getElementById('ots-form-note');
        const emailField = document.getElementById('ots-email-field');
        const emailInput = document.getElementById('ots-email');
        const formKeyInput = container.querySelector('input[name="form_key"]');
        const hiddenEmailInput = document.getElementById('ots-submitted-email');
        const passcodeField = document.getElementById('ots-passcode-field');
        const passcodeInput = document.getElementById('ots-passcode');
        const formConfig = JSON.parse(container.dataset.otsLogin || '{}');
        const configuredResendTimeoutSeconds = parseInt(formConfig.resendTimeoutSeconds, 10);
        const resendTimeoutSeconds = configuredResendTimeoutSeconds > 0
            ? configuredResendTimeoutSeconds
            : DEFAULT_RESEND_TIMEOUT_SECONDS;
        let isPasscodeStep = false;
        let resendTimerId = null;
        let resendSecondsRemaining = resendTimeoutSeconds;

        const renderMessage = function (text, type) {
            messages.style.display = 'block';
            messages.replaceChildren();

            const wrapper = document.createElement('div');
            wrapper.className = 'message ' + type;

            const content = document.createElement('div');
            content.textContent = text;

            wrapper.appendChild(content);
            messages.appendChild(wrapper);
        };

        const stopResendCountdown = function () {
            if (resendTimerId) {
                clearInterval(resendTimerId);
                resendTimerId = null;
            }
        };

        const renderResendCountdown = function () {
            const minutes = Math.floor(resendSecondsRemaining / 60);
            const seconds = resendSecondsRemaining % 60;
            const label = formConfig.getAnotherCodeCountdownLabel || '';

            resendCountdown.textContent = label
                .replace('%1', minutes)
                .replace('%2', seconds);
        };

        const startResendCountdown = function () {
            stopResendCountdown();
            resendSecondsRemaining = resendTimeoutSeconds;
            resendCodeAction.style.display = '';
            resendCountdown.style.display = '';
            resendCodeButton.style.display = 'none';
            renderResendCountdown();

            resendTimerId = setInterval(function () {
                resendSecondsRemaining -= 1;

                if (resendSecondsRemaining <= 0) {
                    stopResendCountdown();
                    resendCountdown.style.display = 'none';
                    resendCountdown.textContent = '';
                    resendCodeButton.style.display = '';
                    return;
                }

                renderResendCountdown();
            }, 1000);
        };

        const restoreEmailStep = function () {
            stopResendCountdown();
            isPasscodeStep = false;
            hiddenEmailInput.value = '';
            emailField.style.display = '';
            emailInput.disabled = false;
            emailInput.setAttribute('data-validate', EMAIL_VALIDATE);
            passcodeField.style.display = 'none';
            passcodeInput.disabled = true;
            passcodeInput.value = '';
            passcodeInput.removeAttribute('data-validate');
            note.textContent = formConfig.emailStepNote || '';
            submitLabel.textContent = formConfig.getCodeLabel || '';
            resendCodeAction.style.display = 'none';
            resendCountdown.style.display = '';
            resendCountdown.textContent = '';
            resendCodeButton.style.display = 'none';
            actionsToolbar.style.display = '';
            actionsToolbar.style.justifyContent = '';
            actionsToolbar.style.alignItems = '';
            submitButtonWrapper.style.marginLeft = '';
        };

        const switchToPasscodeStep = function (email) {
            isPasscodeStep = true;
            hiddenEmailInput.value = email;
            emailField.style.display = 'none';
            emailInput.value = '';
            emailInput.disabled = true;
            emailInput.removeAttribute('data-validate');
            passcodeField.style.display = '';
            passcodeInput.disabled = false;
            passcodeInput.setAttribute('data-validate', PASSCODE_VALIDATE);
            note.textContent = formConfig.passcodeStepNote || '';
            submitLabel.textContent = formConfig.loginLabel || '';
            actionsToolbar.style.display = 'flex';
            actionsToolbar.style.justifyContent = 'space-between';
            actionsToolbar.style.alignItems = 'center';
            submitButtonWrapper.style.marginLeft = 'auto';
            startResendCountdown();
            passcodeInput.focus();
        };

        const isFormValid = function () {
            return $(container).validation() && $(container).validation('isValid');
        };

        passcodeInput.disabled = true;

        resendCodeButton.addEventListener('click', function () {
            restoreEmailStep();
        });

        submitButton.addEventListener('click', async function () {
            if (!isFormValid()) {
                return;
            }

            submitButton.disabled = true;
            messages.style.display = 'none';

            try {
                const response = await fetch(formConfig.graphQlUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(
                        isPasscodeStep
                            ? {
                                query: 'mutation LoginWithOts($email: String!, $passcode: String!, $formKey: String!) { loginWithOts(email: $email, passcode: $passcode, formKey: $formKey) { success message } }',
                                variables: {
                                    email: hiddenEmailInput.value,
                                    passcode: passcodeInput.value,
                                    formKey: formKeyInput ? formKeyInput.value : ''
                                }
                            }
                            : {
                                query: 'mutation SendQtsCode($email: String!, $formKey: String!) { sendQtsCode(email: $email, formKey: $formKey) { success message } }',
                                variables: {
                                    email: emailInput.value,
                                    formKey: formKeyInput ? formKeyInput.value : ''
                                }
                            }
                    )
                });

                const payload = await response.json();

                if (payload.errors && payload.errors.length) {
                    if (isPasscodeStep) {
                        restoreEmailStep();
                    }
                    renderMessage(payload.errors[0].message, 'message-error error');
                    return;
                }

                if (isPasscodeStep) {
                    renderMessage(payload.data.loginWithOts.message, 'message-success success');
                    window.location.href = formConfig.accountUrl;
                    return;
                }

                renderMessage(payload.data.sendQtsCode.message, 'message-success success');
                switchToPasscodeStep(emailInput.value);
            } catch (error) {
                if (!isPasscodeStep) {
                    restoreEmailStep();
                }
                renderMessage(formConfig.requestErrorMessage || '', 'message-error error');
            } finally {
                submitButton.disabled = false;
            }
        });
    };
});
