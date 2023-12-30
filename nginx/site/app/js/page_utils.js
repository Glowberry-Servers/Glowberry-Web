
/**
 * Silently creates a hidden form and submits it to the specified URL with the specified data,
 * effectively redirecting the user to the specified URL using a POST request
 * @param url The URL to redirect to
 * @param data The json-like data to send with the request
 */
export function redirectWithPost(url, data) {

    // Creates a new hidden form and appends it to the document body
    let form = document.createElement('form');
    document.body.appendChild(form);
    form.style.display = 'none';
    form.method = 'post';  // Set the form method to POST so that the data is not visible in the URL
    form.action = url;  // Set the form action to the specified URL

    // Iterate over the data object and create a hidden input for each key-value pair
    for (let name in data) {
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = data[name];
        form.appendChild(input);
    }

    // Submit the form, effectively redirecting the user to the specified URL with the specified data
    form.submit();
}