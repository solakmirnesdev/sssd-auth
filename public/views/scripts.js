async function handleSubmit(event, formId, endpoint) {
    event.preventDefault();

    let formData = new FormData(document.getElementById(formId));
    let data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    try {
        let response = await fetch(endpoint, {
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
