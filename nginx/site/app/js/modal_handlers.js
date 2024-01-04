// Registers all the buttons that open modal forms.
import {createAjaxRequestFor, generalOnReadyStateHandler} from "/app/js/page_utils.js";

const buttonRegistry = [
    document.getElementById('edit-display-name-button'),
    document.getElementById("change-password-button"),
    document.getElementById("change-profile-picture-button"),
    document.getElementById("change-wallpaper-button"),
    document.getElementById("delete-account-button"),
    document.getElementById("change-role-button"),
    document.getElementById("allocate-resources-button"),
    document.getElementById("reset-password-button"),
    document.getElementById("new-server-button")
];

// Registers all the buttons inside the modal forms that cancel them.
const modalCancelButtonRegistry = [
    document.getElementById('edit-display-name-modal-cancel'),
    document.getElementById("change-password-modal-cancel"),
    document.getElementById("change-profile-picture-modal-cancel"),
    document.getElementById("change-wallpaper-modal-cancel"),
    document.getElementById("delete-account-modal-cancel"),
    document.getElementById("change-role-modal-cancel"),
    document.getElementById("allocate-resources-modal-cancel"),
    document.getElementById("reset-password-modal-cancel"),
    document.getElementById("new-server-modal-cancel")
];

// Registers all the buttons inside the modal forms that submit them.
const modalSubmitButtonRegistry = [
    document.getElementById('edit-display-name-modal-apply'),
    document.getElementById("change-password-modal-apply"),
    document.getElementById("change-profile-picture-modal-apply"),
    document.getElementById("change-wallpaper-modal-apply"),
    document.getElementById("delete-account-modal-apply"),
    document.getElementById("change-role-modal-apply"),
    document.getElementById("allocate-resources-modal-apply"),
    document.getElementById("reset-password-modal-apply"),
    document.getElementById("new-server-modal-apply")
];

const modalSubmitButtonFunctionMappings = {
    "edit-display-name-modal-apply": editDisplayName,
    "change-password-modal-apply": changePassword,
    "change-profile-picture-modal-apply": changeProfilePicture,
    "change-wallpaper-modal-apply": changeWallpaper,
    "delete-account-modal-apply": deleteAccount,
    "change-role-modal-apply": changeRole,
    "allocate-resources-modal-apply": allocateResources,
    "reset-password-modal-apply": resetPassword,
    "new-server-modal-apply": createServer
}

// Adds event listeners to all the buttons that open modal forms.
for (let i = 0; i < buttonRegistry.length; i++) {

    if (buttonRegistry[i] === null) continue;
    buttonRegistry[i].addEventListener("click", openModalForm);
}

// Adds event listeners to all the buttons that close modal forms.
for (let i = 0; i < modalCancelButtonRegistry.length; i++) {

    if (modalCancelButtonRegistry[i] === null) continue;
    modalCancelButtonRegistry[i].addEventListener("click", closeModalForm);
}

// Adds event listeners to all the buttons that submit modal forms.
for (let i = 0; i < modalSubmitButtonRegistry.length; i++) {

    if (modalSubmitButtonRegistry[i] === null) continue;
    modalSubmitButtonRegistry[i].addEventListener("click", modalSubmitButtonFunctionMappings[modalSubmitButtonRegistry[i].id]);
}

/**
 * Opens the modal form associated with the button that was clicked.
 */
function openModalForm() {
    let modalPrefix = event.currentTarget.id.replace("-button", "");
    document.getElementById(modalPrefix + "-modal").classList.add('show');
}

/**
 * Closes the modal form associated with the button that was clicked.
 */
function closeModalForm() {
    let modal = event.currentTarget.id.replace("-cancel", "");
    document.getElementById(modal).classList.remove('show');
}


/**
 * Submits the modal form to change the display name of the user,
 * redirecting the accessor to the profile page upon success.
 * @return void
 */
function editDisplayName() {

    let displayName = document.getElementById('display-name-input').value;
    let targetUser = document.getElementById('true-name').innerText;

    // If the target user is undefined, null or empty, then its invalid.
    if (targetUser === undefined || targetUser === null || targetUser === "") {
        return;
    }

    // Prepares the target user for the AJAX request.
    targetUser = targetUser.replace("@", "");  // Takes out the @ symbol.
    let ajax = createAjaxRequestFor("/app/php/operations/change_display_name.php")

    ajax.onreadystatechange = function() {
        generalOnReadyStateHandler(ajax)
    }

    ajax.send("display_name=" + displayName + "&target_user=" + targetUser + "&modal_id=" + event.currentTarget.id.replace("-apply", ""));
}

/**
 * Changes the password of the user, verifying the old password and redirecting the accessor
 * to the login page upon success.
 * @return void
 */
function changePassword() {

    let currentPassword = document.getElementById('current-password-input').value;
    let newPassword = document.getElementById('new-password-input').value;
    let confirmPassword = document.getElementById('confirm-password-input').value;

    let targetUser = document.getElementById('true-name').innerText;

    // If the target user is undefined, null or empty, then its invalid.
    if (targetUser === undefined || targetUser === null || targetUser === "") {
        return;
    }

    // Prepares the target user for the AJAX request.
    targetUser = targetUser.replace("@", "");  // Takes out the @ symbol.
    let ajax = createAjaxRequestFor("/app/php/operations/change_password.php")

    ajax.onreadystatechange = function() {
        generalOnReadyStateHandler(ajax)
    }

    ajax.send("old_password=" + currentPassword + "&new_password=" + newPassword + "&confirm_password=" + confirmPassword + "&target_user=" + targetUser + "&modal_id=" + event.currentTarget.id.replace("-apply", ""));
}

/**
 * Sends a request to change the profile picture of the user.
 * @return void
 */
function changeProfilePicture() {
    changeImageResource("profile_picture");
}

/**
 * Sends a request to change the wallpaper of the user.
 * @return void
 */
function changeWallpaper() {
    changeImageResource("wallpaper");
}

/**
 * Parses out the image URL from the modal form and sends a request to change the image resource, which
 * will change the profile picture or wallpaper of the user based on the resource type.
 * @param resourceType The type of resource to change, either "profile_picture" or "wallpaper".
 * @return void
 */
function changeImageResource(resourceType) {

    let targetUser = document.getElementById('true-name').innerText;
    let url = document.getElementById(resourceType.replace("_", "-") + '-input').value;

    // If the target user is undefined, null or empty, then its invalid.
    if (targetUser === undefined || targetUser === null || targetUser === "") {
        return;
    }

    // Prepares the target user for the AJAX request.
    targetUser = targetUser.replace("@", "");  // Takes out the @ symbol.
    let ajax = createAjaxRequestFor("/app/php/operations/change_image_resource.php")

    ajax.onreadystatechange = function() {
        generalOnReadyStateHandler(ajax)
    }

    ajax.send("image_url=" + url + "&resource_type=" + resourceType + "&target_user=" + targetUser + "&modal_id=" + event.currentTarget.id.replace("-apply", ""));
}

/**
 * Sends a request to delete the account of the user and everything associated with it.
 * @return void
 */
function deleteAccount() {

    let targetUser = document.getElementById('true-name').innerText;
    let password = document.getElementById('password-input').value;

    // If the target user is undefined, null or empty, then its invalid.
    if (targetUser === undefined || targetUser === null || targetUser === "") {
        return;
    }

    // Prepares the target user for the AJAX request.
    targetUser = targetUser.replace("@", "");  // Takes out the @ symbol.
    let ajax = createAjaxRequestFor("/app/php/operations/delete_account.php")

    ajax.onreadystatechange = function() {
        generalOnReadyStateHandler(ajax)
    }

    ajax.send("password=" + password + "&target_user=" + targetUser + "&modal_id=" + event.currentTarget.id.replace("-apply", ""));
}

/**
 * Sends a request to change the role of the user.
 * @return void
 */
function changeRole() {

    let targetUser = document.getElementById('true-name').innerText;
    let role = document.getElementById('role-input').value;

    // If the target user is undefined, null or empty, then its invalid.
    if (targetUser === undefined || targetUser === null || targetUser === "") {
        return;
    }

    // Prepares the target user for the AJAX request.
    targetUser = targetUser.replace("@", "");  // Takes out the @ symbol.
    let ajax = createAjaxRequestFor("/app/php/operations/change_role.php")

    ajax.onreadystatechange = function() {
        generalOnReadyStateHandler(ajax)
    }

    ajax.send("new_role=" + role + "&target_user=" + targetUser + "&modal_id=" + event.currentTarget.id.replace("-apply", ""));
}

/**
 * Sends a request to allocate resources to the user. At the moment, this is only used to allow the user
 * to use more RAM.
 * @return void
 */
function allocateResources() {

    let targetUser = document.getElementById('true-name').innerText;
    let ram = document.getElementById('ram-input').value;

    // If the target user is undefined, null or empty, then its invalid.
    if (targetUser === undefined || targetUser === null || targetUser === "") {
        return;
    }

    // Prepares the target user for the AJAX request.
    targetUser = targetUser.replace("@", "");  // Takes out the @ symbol.
    let ajax = createAjaxRequestFor("/app/php/operations/allocate_resources.php")

    ajax.onreadystatechange = function() {
        generalOnReadyStateHandler(ajax)
    }

    ajax.send("ram=" + ram + "&target_user=" + targetUser + "&modal_id=" + event.currentTarget.id.replace("-apply", ""));
}

/**
 * Sends a request to reset the password of the user by resetting it to a one-time use password
 * displayed on the screen.
 * @return void
 */
function resetPassword() {

    let targetUser = document.getElementById('true-name').innerText;
    let password = document.querySelector(".one-time-password-container p").innerText;

    // If the target user is undefined, null or empty, then its invalid.
    if (targetUser === undefined || targetUser === null || targetUser === "") {
        return;
    }

    // Prepares the target user for the AJAX request.
    targetUser = targetUser.replace("@", "");  // Takes out the @ symbol.
    let ajax = createAjaxRequestFor("/app/php/operations/reset_password.php")

    ajax.onreadystatechange = function() {
        generalOnReadyStateHandler(ajax)
    }

    ajax.send("target_user=" + targetUser + "&one_time_code=" + password + "&modal_id=" + event.currentTarget.id.replace("-apply", ""));
}

/**
 * Sends a request to create a new server.
 * @return void
 */
function createServer() {

    let serverName = document.getElementById('server-name-input').value;
    let serverType = document.getElementById('server-type-input').value;
    let serverVersion = document.getElementById('server-version-input').value;
    let javaVersion = document.getElementById('java-version-input').value;

    // Prepares the target user for the AJAX request.
    let ajax = createAjaxRequestFor("/app/php/operations/build_server.php")

    ajax.onreadystatechange = function() {
        generalOnReadyStateHandler(ajax)
    }

    ajax.send("server_name=" + serverName + "&server_type=" + serverType + "&server_version=" + serverVersion + "&java_version=" + javaVersion + "&modal_id=" + event.currentTarget.id.replace("-apply", ""));
}