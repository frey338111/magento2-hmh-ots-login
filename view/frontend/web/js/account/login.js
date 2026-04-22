define([
    'jquery',
    'mage/validation'
], function ($) {
    'use strict';

    const EMAIL_VALIDATE = '{required:true, \'validate-email\':true}';
    const PASSCODE_VALIDATE = '{required:true}';

    return function (config, element) {
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

        const restoreEmailStep = function () {
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
            passcodeInput.focus();
        };

        passcodeInput.disabled = true;

        submitButton.addEventListener('click', async function () {
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
                                query: 'mutation LoginWithOts($email: String!, $passcode: String!) { loginWithOts(email: $email, passcode: $passcode) { success message } }',
                                variables: {
                                    email: hiddenEmailInput.value,
                                    passcode: passcodeInput.value
                                }
                            }
                            : {
                                query: 'mutation SendQtsCode($email: String!) { sendQtsCode(email: $email) { success message } }',
                                variables: {
                                    email: emailInput.value
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
