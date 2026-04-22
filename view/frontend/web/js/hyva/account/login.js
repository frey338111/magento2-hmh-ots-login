(function (window) {
    'use strict';

    const EMAIL_VALIDATE = '{required:true, \'validate-email\':true}';
    const PASSCODE_VALIDATE = '{required:true}';

    window.initHmhOtsLogin = function (element) {
        const container = element;
        const messages = document.getElementById('ots-request-messages');
        const submitButton = document.getElementById('send-ots-code');
        const submitLabel = document.getElementById('ots-submit-label');
        const note = document.getElementById('ots-form-note');
        const emailField = document.getElementById('ots-email-field');
        const emailInput = document.getElementById('ots-email');
        const hiddenEmailInput = document.getElementById('ots-submitted-email');
        const passcodeField = document.getElementById('ots-passcode-field');
        const passcodeInput = document.getElementById('ots-passcode');
        const formConfig = JSON.parse(container.dataset.otsLogin || '{}');
        let isPasscodeStep = false;

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

        const restoreEmailStep = function () {
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
            passcodeInput.focus();
        };

        const getRequestPayload = function () {
            if (isPasscodeStep) {
                return {
                    query: 'mutation LoginWithOts($email: String!, $passcode: String!) { loginWithOts(email: $email, passcode: $passcode) { success message } }',
                    variables: {
                        email: hiddenEmailInput.value,
                        passcode: passcodeInput.value
                    }
                };
            }

            return {
                query: 'mutation SendQtsCode($email: String!) { sendQtsCode(email: $email) { success message } }',
                variables: {
                    email: emailInput.value
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
