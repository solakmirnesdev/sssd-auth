/**
 * Handles form submission asynchronously.
 *
 * This function prevents the default form submission behavior,
 * collects the form data, sends a POST request to the specified endpoint,
 * and displays the server response message on the page.
 *
 * @param {Event} event - The form submission event.
 * @param {string} formId - The ID of the form being submitted.
 * @param {string} endpoint - The API endpoint to send the form data to.
 */
async function handleSubmit(event, formId, endpoint) {
    event.preventDefault();

    // Collect form data
    let formData = new FormData(document.getElementById(formId));
    let data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    try {
        // Send POST request to the endpoint with form data
        let response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        // Process the server response
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
