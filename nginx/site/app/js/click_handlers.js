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

if (document.querySelector("#user-server-permissions-update-button") !== null)
    document.querySelector("#user-server-permissions-update-button").addEventListener("click", updateServerUserPermissions);

if (document.querySelector("#server-type-input") !== null)
document.querySelector("#server-type-input").addEventListener("change", changeServerVersionsList);

// Handles the click events for the all-users page.
const users = document.getElementsByClassName("user");

for (let i = 0; i < users.length; i++)
    users[i].addEventListener("click", sendToUserProfilePage);

// Gets both the webapp and server permission checkboxes
const webAppCheckboxes = document.getElementsByClassName("webapp-permission-checkbox");
const serverCheckboxes = document.getElementsByClassName("server-permission-checkbox");

// Adds the calculatePermissionsInteger function to both

for (let i = 0; i < webAppCheckboxes.length; i++)
    webAppCheckboxes[i].addEventListener("change", calculatePermissionsInteger);

for (let i = 0; i < serverCheckboxes.length; i++)
    serverCheckboxes[i].addEventListener("change", calculatePermissionsInteger);

const serverDeleteButtons = document.getElementsByClassName("server-delete-button");

for (let i = 0; i < serverDeleteButtons.length; i++)
    serverDeleteButtons[i].addEventListener("click", deleteServer);

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
 * Gets the server type from the server type input and sends an ajax request to get the server versions
 * for the specified server type. Then, changes the innerHTML of the options for the server versions to the
 * received HTML.
 */
function changeServerVersionsList() {

        event.preventDefault();

        let selectedType = document.querySelector("#server-type-input").value;

        // Creates an ajax request with the server_name payload.
        let ajax = createAjaxRequestFor("/app/php/operations/get_server_versions_for.php");
        ajax.onreadystatechange = function () {

            if (ajax.readyState === 4 && ajax.status === 200) {

                let html = ajax.responseText;
                let selectElement = document.querySelector("#server-version-input");
                selectElement.innerHTML = html;
            }
        };

        ajax.send("server_type=" + selectedType);
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
 * Grabs the elements from the page and sends an ajax request to update the server user permissions.
 * @returns void
 */
function updateServerUserPermissions() {

    event.preventDefault();

    // Gets the server name and user's name from the page
    let targetUser = document.querySelector("#user-search-permissions").value;
    let serverName = document.querySelector("#server-search-permissions").value;
    let permissionsInteger = document.querySelector("#server-permissions-integer").value;

    targetUser = targetUser.replace("@", "");

    // Creates an ajax request with the server_name and user_name payloads.
    let ajax = createAjaxRequestFor("/app/php/operations/update_server_user_permissions.php");
    ajax.onreadystatechange = function () {
        generalOnReadyStateHandler(ajax)
    };

    ajax.send("server_name=" + serverName + "&target_user=" + targetUser + "&permissions_integer=" + permissionsInteger);
}

/**
 * Gets the assigned permission value from the checkbox and adds it to the result value in the same
 * calculator div.
 * @returns void
 */
function calculatePermissionsInteger() {

    // Gets the permission value from the checkbox and the result value from the result input
    let calculator = event.currentTarget.parentElement.parentElement;
    let resultValue = calculator.querySelector("p span");
    let allPermissions = document.getElementsByClassName(event.currentTarget.className);

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
 * Sends a request to delete the server.
 * @returns void
 */
function deleteServer() {

    let serverName = event.currentTarget.parentElement.parentElement.querySelector(".server-card-name").innerText;

    // Creates an ajax request with the server_name payload.
    let ajax = createAjaxRequestFor("/app/php/operations/delete_server.php");

    ajax.onreadystatechange = function () {
        generalOnReadyStateHandler(ajax)
    };

    ajax.send("server_name=" + serverName);
}

/**
 * Logs the user out of the system.
 * @returns void
 */
function logOut() {
    window.location.href = "/app/php/operations/logout.php";
    event.preventDefault();
}