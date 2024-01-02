import {redirectWithPost} from "/app/js/page_utils.js";

// Handles the click event for the log-out button.
if (document.querySelector("#logout") !== null)
    document.querySelector("#logout").addEventListener("click", logOut);

// Handles the click events for the all-users page.
let users = document.getElementsByClassName("user");

for (let i = 0; i < users.length; i++)
    users[i].addEventListener("click", sendToUserProfilePage);

// Handles the click event for the permissions integer calculator.
let checkboxes = document.getElementsByClassName("permission-checkbox");

for (let i = 0; i < checkboxes.length; i++)
    checkboxes[i].addEventListener("change", calculatePermissionsInteger);


/**
 * Sends the user to the dashboard page.
 * @returns void
 */
function sendToDashboard() {
    window.location.href = "/app/php/web/dashboard.php";
    event.preventDefault()
}

/**
 * Sends the user to the selected user's profile page.
 * @returns void
 */
function sendToUserProfilePage() {

    event.preventDefault()

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
 * This method is meant to be used in the permissions integer calculator, at the permissions
 * page for administrators only. It calculates the permissions integer based on the checkboxes
 * active.
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
    window.location.href = "/app/php/logout.php";
    event.preventDefault()

}