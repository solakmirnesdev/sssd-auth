document.addEventListener('DOMContentLoaded', function () {
    async function handleSubmit(event, formId, endpoint) {
        event.preventDefault();

        let formData = new FormData(document.getElementById(formId));
        let data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        // Include hCaptcha response if visible
        const captchaContainer = document.getElementById('captcha-container');
        if (captchaContainer.style.display === 'block') {
            data['h-captcha-response'] = document.querySelector('[name="h-captcha-response"]').value;
        }

        try {
            let response = await fetch(endpoint.replace('localhost', '127.0.0.1'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            let result = await response.json();
            let messageElement = document.getElementById('message');
            if (response.ok) {
                messageElement.innerHTML = `<h5>${result.message}</h5>`;
                if (result.success) {
                    // Reset failed attempts on successful login
                    sessionStorage.removeItem('failedAttempts');
                    captchaContainer.style.display = 'none';
                }
            } else {
                let failedAttempts = parseInt(sessionStorage.getItem('failedAttempts')) || 0;
                failedAttempts += 1;
                sessionStorage.setItem('failedAttempts', failedAttempts);

                messageElement.innerHTML = `<h5>${result.error}</h5>`;
                if (failedAttempts >= 3 || result.captcha_needed) {
                    captchaContainer.style.display = 'block';
                }
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('message').innerHTML = '<h5>There was an error processing your request.</h5>';
        }
    }

    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.onsubmit = (event) => handleSubmit(event, 'register-form', '/api/register');
    }

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.onsubmit = (event) => handleSubmit(event, 'loginForm', '/api/login');
    }
});
