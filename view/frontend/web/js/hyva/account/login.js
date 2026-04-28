(function (window) {
    'use strict';

    const EMAIL_VALIDATE = '{required:true, \'validate-email\':true}';
    const PASSCODE_VALIDATE = '{required:true}';
    const DEFAULT_RESEND_TIMEOUT_SECONDS = 300;

    window.initHmhOtsLogin = function (element) {
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

        const setVisibility = function (target, isVisible) {
            if (!target) {
                return;
            }

            target.classList.toggle('hidden', !isVisible);
            target.hidden = !isVisible;
        };

        const resetMessages = function () {
            if (!messages) {
                return;
            }

            messages.replaceChildren();
            setVisibility(messages, false);
        };

        const renderMessage = function (text, type) {
            if (!messages) {
                if (window.dispatchMessages) {
                    window.dispatchMessages([{ type: type.indexOf('success') !== -1 ? 'success' : 'error', text: text }], 5000);
                }
                return;
            }

            messages.replaceChildren();

            const wrapper = document.createElement('div');
            wrapper.className = 'message ' + type;

            const content = document.createElement('div');
            content.textContent = text;

            wrapper.appendChild(content);
            messages.appendChild(wrapper);
            setVisibility(messages, true);

            if (window.dispatchMessages) {
                window.dispatchMessages([{ type: type.indexOf('success') !== -1 ? 'success' : 'error', text: text }], 5000);
            }
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
            setVisibility(resendCodeAction, true);
            setVisibility(resendCountdown, true);
            setVisibility(resendCodeButton, false);
            renderResendCountdown();

            resendTimerId = setInterval(function () {
                resendSecondsRemaining -= 1;

                if (resendSecondsRemaining <= 0) {
                    stopResendCountdown();
                    setVisibility(resendCountdown, false);
                    resendCountdown.textContent = '';
                    setVisibility(resendCodeButton, true);
                    return;
                }

                renderResendCountdown();
            }, 1000);
        };

        const restoreEmailStep = function () {
            stopResendCountdown();
            isPasscodeStep = false;
            hiddenEmailInput.value = '';
            setVisibility(emailField, true);
            emailInput.disabled = false;
            emailInput.setAttribute('data-validate', EMAIL_VALIDATE);
            emailInput.required = true;
            setVisibility(passcodeField, false);
            passcodeInput.disabled = true;
            passcodeInput.required = false;
            passcodeInput.value = '';
            passcodeInput.removeAttribute('data-validate');
            note.textContent = formConfig.emailStepNote || '';
            submitLabel.textContent = formConfig.getCodeLabel || '';
            setVisibility(resendCodeAction, false);
            setVisibility(resendCountdown, true);
            resendCountdown.textContent = '';
            setVisibility(resendCodeButton, false);
            actionsToolbar.style.justifyContent = '';
            actionsToolbar.style.alignItems = '';
            submitButtonWrapper.style.marginLeft = '';
        };

        const switchToPasscodeStep = function (email) {
            isPasscodeStep = true;
            hiddenEmailInput.value = email;
            setVisibility(emailField, false);
            emailInput.value = '';
            emailInput.disabled = true;
            emailInput.required = false;
            emailInput.removeAttribute('data-validate');
            setVisibility(passcodeField, true);
            passcodeInput.disabled = false;
            passcodeInput.required = true;
            passcodeInput.setAttribute('data-validate', PASSCODE_VALIDATE);
            note.textContent = formConfig.passcodeStepNote || '';
            submitLabel.textContent = formConfig.loginLabel || '';
            actionsToolbar.style.justifyContent = 'space-between';
            actionsToolbar.style.alignItems = 'center';
            submitButtonWrapper.style.marginLeft = 'auto';
            startResendCountdown();
            passcodeInput.focus();
        };

        const getRequestPayload = function () {
            if (isPasscodeStep) {
                return {
                    query: 'mutation LoginWithOts($email: String!, $passcode: String!, $formKey: String!) { loginWithOts(email: $email, passcode: $passcode, formKey: $formKey) { success message } }',
                    variables: {
                        email: hiddenEmailInput.value,
                        passcode: passcodeInput.value,
                        formKey: formKeyInput ? formKeyInput.value : ''
                    }
                };
            }

            return {
                query: 'mutation SendQtsCode($email: String!, $formKey: String!) { sendQtsCode(email: $email, formKey: $formKey) { success message } }',
                variables: {
                    email: emailInput.value,
                    formKey: formKeyInput ? formKeyInput.value : ''
                }
            };
        };

        const validateActiveField = function () {
            const activeInput = isPasscodeStep ? passcodeInput : emailInput;

            if (!activeInput) {
                return false;
            }

            return activeInput.reportValidity();
        };

        restoreEmailStep();

        resendCodeButton.addEventListener('click', function () {
            restoreEmailStep();
        });

        submitButton.addEventListener('click', async function () {
            if (!validateActiveField()) {
                return;
            }

            submitButton.disabled = true;
            resetMessages();

            try {
                const response = await fetch(formConfig.graphQlUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(getRequestPayload())
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
}(window));
