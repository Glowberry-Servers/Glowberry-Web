/**
 * Handles the entire login form submission process, from the data submission to the response handling
 * @returns false - Stops the program from submitting the form normally
 */
function handleLoginFormSubmission() {

    // Get the data submitted in the form
    let username = document.getElementById("login-username").value;
    let password = document.getElementById("login-password").value;
    let errorMessageDiv = document.getElementById("login-error");

    // Create an ajax request instance for the login form and handle the response in the standard way
    let ajax = createAjaxRequestFor("/reception/php/userops/login.php");
    ajax.onreadystatechange = function() { generalFormReadyStateHandler(ajax) };

    // Send the request with the form data
    ajax.send("username=" + username + "&password=" + password);

    // Prevent the form from submitting normally
    return false;
}

/**
 * Handles the entire signup form submission process, from the data submission to the response handling
 * @returns false - Stops the program from submitting the form normally
 */
function handleSignupFormSubmission() {

    // Get the data submitted in the form
    let username = document.getElementById("signup-username").value;
    let password = document.getElementById("signup-password").value;
    let confirm = document.getElementById("signup-confirm").value;

    // Create an ajax request instance for the signup form and handle the response in the standard way
    let ajax = createAjaxRequestFor("/reception/php/userops/signup.php");
    ajax.onreadystatechange = function() { generalFormReadyStateHandler(ajax) };

    // Send the request with the form data
    ajax.send("username=" + username + "&password=" + password + "&confirm_password=" + confirm);

    // Prevent the form from submitting normally
    return false;
}

/**
 * Handles the entire password recovery form submission process, from the data submission to the response handling
 * @returns false - Stops the program from submitting the form normally
 */
function handleRecoveryFormSubmission() {

    // Get the data submitted in the form
    let code = document.getElementById("recovery-code").value;
    let new_password = document.getElementById("new-password").value;

    // Create an ajax request instance for the recovery form and handle the response in the standard way
    let ajax = createAjaxRequestFor("/reception/php/userops/password_recovery.php");
    ajax.onreadystatechange = function() { generalFormReadyStateHandler(ajax) };

    // Send the request with the form data
    ajax.send("security_code=" + code + "&new_password=" + new_password);

    // Prevent the form from submitting normally
    return false;
}

/**
 * Creates an AJAX request given a specific resource page in the local storage.
 * @param page The path to the page from the document root
 */
function createAjaxRequestFor(page) {

    let ajax = new XMLHttpRequest();
    ajax.open("POST", page, true);
    ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    return ajax;
}

/**
 * Handles the process of verifying the ready state of a form and redirecting the user to the specified URL
 * afterwards, and displaying an error message based on a received "element-name" property in the JSON response
 *
 * @param ajax The AJAX request object to use
 */
function generalFormReadyStateHandler(ajax) {

    if (ajax.readyState === 4 && ajax.status === 200) {

        let json = JSON.parse(ajax.responseText);

        // If the sign-up was successful, redirect to the dashboard, if not, display the error message
        if (json.method === "POST" ) redirectWithPost(json.href, json);
        else if (json.method === "GET") window.location.href = json.href;
        else document.getElementById(json["element-name"]).innerText = json.error;
    }

}


/**
 * Silently creates a hidden form and submits it to the specified URL with the specified data,
 * effectively redirecting the user to the specified URL using a POST request
 * @param url The URL to redirect to
 * @param data The json-like data to send with the request
 */
function redirectWithPost(url, data) {

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
