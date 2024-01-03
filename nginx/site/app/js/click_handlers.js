import {createAjaxRequestFor, generalOnReadyStateHandler, redirectWithPost} from "/app/js/page_utils.js";

// Handles the click event for the log-out button.
if (document.querySelector("#logout") !== null)
    document.querySelector("#logout").addEventListener("click", logOut);

if (document.querySelector("#set-role") !== null)
    document.querySelector("#set-role").addEventListener("click", setRole);

if (document.querySelector('#delete-role') !== null)
    document.querySelector('#delete-role').addEventListener("click", deleteRole);

if (document.querySelector("#user-search-permissions-button") !== null)
    document.querySelector("#user-search-permissions-button").addEventListener("click", sendToUserPermissionsPage);

if (document.querySelector("#server-search-permissions-button") !== null)
    document.querySelector("#server-search-permissions-button").addEventListener("click", sendToUserPermissionsPage);

// Handles the click events for the all-users page.
const users = document.getElementsByClassName("user");

for (let i = 0; i < users.length; i++)
    users[i].addEventListener("click", sendToUserProfilePage);

// Handles the click event for the permissions integer calculator.
const checkboxes = document.getElementsByClassName("permission-checkbox");

for (let i = 0; i < checkboxes.length; i++)
    checkboxes[i].addEventListener("change", calculatePermissionsInteger);


/**
 * Sends the user to the dashboard page.
 * @returns void
 */
function sendToDashboard() {
    window.location.href = "/app/php/web/dashboard.php";
    event.preventDefault();
}

/**
 * Sends the user to the selected user's profile page.
 * @returns void
 */
function sendToUserProfilePage() {

    event.preventDefault();

    // Parses the target_user from the assigned target
    let targetUser = event.currentTarget.innerText.split(/([@\n])/)[2];

    // If the target_user is undefined, null or empty, we're going to be sent to our own profile page
    if (targetUser === undefined || targetUser === null || targetUser.length === 0) {
        redirectWithPost("/app/php/web/user.php", {});
        return;
    }

    // Otherwise, we're going to be sent to the target_user's profile page
    redirectWithPost("/app/php/web/user.php", {target_user: targetUser});
}

/**
 * Sends the user to the permissions page with the specified server and user search payloads.
 * These will be the target server and target user for the permissions page.
 */
function sendToUserPermissionsPage() {

    event.preventDefault();

    // Parses out the server search and the user search
    let serverSearch = document.querySelector("#server-search-permissions").value;
    let userSearch = document.querySelector("#user-search-permissions").value;

    // Replace the @ symbol if it exists in the user search
    if (userSearch !== undefined|| userSearch !== "")
        userSearch.replace("@", "");

    // Creates an ajax request with the server search and user search payloads.
    let ajax = createAjaxRequestFor("/app/php/web/permissions.php");
    ajax.onreadystatechange = function () {
        generalOnReadyStateHandler(ajax)
    };

    ajax.send("target_server=" + serverSearch + "&target_user=" + userSearch + "&redirect=true");
}

/**
 * Grabs the elements from the page and sends an ajax request to set the role specified.
 * If accepted, inserts or updates the role in the database.
 * @returns void
 */
function setRole() {

    event.preventDefault();

    // Gets the role_name and role_integer from the page
    let role_name = document.querySelector("#role-name").value;
    let role_integer = document.querySelector("#role-permission-integer").value;

    // If the role_name is undefined, null or empty, it's 0.
    if (role_integer === "" || role_integer === null || role_integer === undefined)
        role_integer = 0;

    // Creates an ajax request with the role_name and integer payloads.
    let ajax = createAjaxRequestFor("/app/php/operations/set_role.php");
    ajax.onreadystatechange = function () {
        generalOnReadyStateHandler(ajax)
    };

    ajax.send("role_name=" + role_name + "&permissions_integer=" + role_integer);
}

/**
 * Grabs the elements from the page and sends an ajax request to delete the role specified.
 * If accepted, inserts or updates the role in the database.
 * @returns void
 */
function deleteRole() {

    event.preventDefault();

    // Gets the role_name from the page
    let role_name = document.querySelector("#role-name").value;

    // Creates an ajax request with the role_name and integer payloads.
    let ajax = createAjaxRequestFor("/app/php/operations/delete_role.php");
    ajax.onreadystatechange = function () {
        generalOnReadyStateHandler(ajax)
    };

    ajax.send("role_name=" + role_name);
}

/**
 * This method is meant to be used in the permissions integer calculator, at the permissions
 * page for administrators only. It calculates the permissions integer based on the checkboxes
 * active.
 * @returns void
 */
function calculatePermissionsInteger() {

    // Gets the permission value from the checkbox and the result value from the result input
    let resultValue = document.querySelector("#result-value");
    let allPermissions = document.getElementsByClassName("permission-checkbox");
    resultValue.innerText = 0;  // Resets the result value to 0

    for (let i = 0; i < allPermissions.length; i++) {

        let permissionValue = parseInt(allPermissions[i].getAttribute("data-permission-integer"));

        // If the permission value is 0 and the checkbox is checked, the result value is 0.
        // No permissions override the other permissions.
        if (permissionValue === 0 && allPermissions[i].getElementsByTagName('input')[0].checked) {
            resultValue.innerText = 0;
            break;
        }

        // If the permission value is -1 and the checkbox is checked, the result value is -1,
        // since -1 is the value for all permissions.
        if (permissionValue === -1 && allPermissions[i].getElementsByTagName('input')[0].checked) {
            resultValue.innerText = -1;
            break;
        }

        if (allPermissions[i].getElementsByTagName('input')[0].checked)
            resultValue.innerText = parseInt(resultValue.innerText) + parseInt(permissionValue);
    }

}

/**
 * Logs the user out of the system.
 * @returns void
 */
function logOut() {
    window.location.href = "/app/php/operations/logout.php";
    event.preventDefault();
}