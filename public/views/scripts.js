/**
 * Handle form submission.
 *
 * This function handles the form submission by preventing the default form submission,
 * collecting form data, sending an async request to the server, and handling the response.
 *
 * @param {Event} event - The form submission event.
 * @param {string} formId - The ID of the form being submitted.
 * @param {string} endpoint - The endpoint to which the form data is submitted.
 */
async function handleSubmit(event, formId, endpoint) {
    event.preventDefault();

    let formData = new FormData(document.getElementById(formId));
    let data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    // Get the hCaptcha response token
    data['h-captcha-response'] = document.querySelector('[name="h-captcha-response"]').value;

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
        } else {
            messageElement.innerHTML = `<h5>${result.error}</h5>`;
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('message').innerHTML = '<h5>There was an error processing your request.</h5>';
    }
}
