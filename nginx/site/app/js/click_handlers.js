import {redirectWithPost} from "/app/js/page_utils.js";

if (document.querySelector(".menu-header-left") !== null)
    document.querySelector(".menu-header-left").addEventListener("click", sendToDashboard);

if (document.querySelector("#logout") !== null)
    document.querySelector("#logout").addEventListener("click", logOut);

if (document.querySelector("#profile-picture") !== null)
    document.querySelector("#profile-picture").addEventListener("click", sendToUserProfilePage);

/**
 * Sends the user to the dashboard page.
 * @returns void
 */
function sendToDashboard() {
    window.location.href = "/app/php/web/dashboard.php";
    event.preventDefault()
}

/**
 * Sends the user to their user page. <br/>
 * We don't need any POST argument here because the user already has the session cookie.
 * @returns void
 */
function sendToUserProfilePage() {
    event.preventDefault()

    let target_user = event.target.getElementsByTagName("target");

    if (target_user === undefined || target_user === null || target_user.length === 0) {
        redirectWithPost("/app/php/web/user.php", {});
        return;
    }

    redirectWithPost("/app/php/web/user.php", {target_user: target_user.text});
}

/**
 * Logs the user out of the system.
 * @returns void
 */
function logOut() {
    window.location.href = "/app/php/logout.php";
    event.preventDefault()
}