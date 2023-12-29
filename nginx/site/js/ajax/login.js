/**
 * Handles the entire login form submission process, from the data submission to the response handling
 * @returns {boolean} - Whether to proceed with the form submission to the next page or not
 */
function handleLoginFormSubmission() {

    // Get the data submitted in the form
    let username = document.getElementById("login-username").value;
    let password = document.getElementById("login-password").value;
    let errorMessageDiv = document.getElementById("login-error");

    // Create an ajax request instance for the login form and set the request method to POST
    let ajax = new XMLHttpRequest();
    ajax.open("POST", "php/userops/login.php", true);
    ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    // When the request is complete, check if the login was successful
    ajax.onreadystatechange = function() {
        if (ajax.readyState === 4 && ajax.status === 200) {

            let json = JSON.parse(ajax.responseText);

            // If the login was successful, redirect to the dashboard, if not, display the error message
            if (json.success) window.location.href = "../../php/app/dashboard.php";
            else errorMessageDiv.innerText = json.error;
        }
    };

    // Send the request with the form data
    ajax.send("username=" + username + "&password=" + password);

    // Prevent the form from submitting normally
    return false;
}
