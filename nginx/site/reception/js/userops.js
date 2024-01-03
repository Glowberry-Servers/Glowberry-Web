import {createAjaxRequestFor, generalOnReadyStateHandler} from "/app/js/page_utils.js";

if (document.querySelector("#login-form") !== null)
    document.querySelector("#login-form").addEventListener("submit", handleLoginFormSubmission);

if (document.querySelector("#signup-form") !== null)
    document.querySelector("#signup-form").addEventListener("submit", handleSignupFormSubmission);

if (document.querySelector("#recovery-form") !== null)
    document.querySelector("#recovery-form").addEventListener("submit", handleRecoveryFormSubmission);

if (document.querySelector("#back") !== null)
    document.querySelector("#back").addEventListener("click", returnToWelcome);

/**
 * Takes the user back to the login/register page
 */
function returnToWelcome() {
    window.location.href = "/reception/welcome.html";
}

/**
 * Handles the entire login form submission process, from the data submission to the response handling
 * @returns void
 */
function handleLoginFormSubmission() {

    // Get the data submitted in the form
    let username = document.getElementById("login-username").value;
    let password = document.getElementById("login-password").value;
    let persistent = document.getElementById("remember-me").checked;

    // Create an ajax request instance for the login form and handle the response in the standard way
    let ajax = createAjaxRequestFor("/reception/php/login.php");
    ajax.onreadystatechange = function () {
        generalOnReadyStateHandler(ajax)
    };

    // Send the request with the form data
    ajax.send("username=" + username + "&password=" + password + "&persistent=" + persistent);

    // Prevent the form from submitting normally
    event.preventDefault();
}

/**
 * Handles the entire signup form submission process, from the data submission to the response handling
 * @returns void
 */
function handleSignupFormSubmission() {

    // Get the data submitted in the form
    let username = document.getElementById("signup-username").value;
    let password = document.getElementById("signup-password").value;
    let confirm = document.getElementById("signup-confirm").value;

    // Create an ajax request instance for the signup form and handle the response in the standard way
    let ajax = createAjaxRequestFor("/reception/php/signup.php");
    ajax.onreadystatechange = function () {
        generalOnReadyStateHandler(ajax)
    };

    // Send the request with the form data
    ajax.send("username=" + username + "&password=" + password + "&confirm_password=" + confirm);

    // Prevent the form from submitting normally
    event.preventDefault();
}

/**
 * Handles the entire password recovery form submission process, from the data submission to the response handling
 * @returns void
 */
function handleRecoveryFormSubmission() {

    // Get the data submitted in the form
    let code = document.getElementById("recovery-code").value;
    let new_password = document.getElementById("new-password").value;

    // Create an ajax request instance for the recovery form and handle the response in the standard way
    let ajax = createAjaxRequestFor("/reception/php/password_recovery.php");
    ajax.onreadystatechange = function () {
        generalOnReadyStateHandler(ajax)
    };

    // Send the request with the form data
    ajax.send("security_code=" + code + "&new_password=" + new_password);

    // Prevent the form from submitting normally
    event.preventDefault();
}
