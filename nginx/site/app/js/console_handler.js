import {createAjaxRequestFor} from "/app/js/page_utils.js";
const serverUUID = document.querySelector('data-server-id').innerText;

document.addEventListener('DOMContentLoaded', function() {

    // Handles the console content updating periodically.
    const console = document.querySelector('#console');
    if (console !== null) setInterval(UpdateConsolePeriodically, 500);

    // Handles any send message button events
    const sendMessageButton = document.querySelector('#send-button');

    if (sendMessageButton !== null) {
        sendMessageButton.addEventListener('click', function() {
            SendMessage();
        });
    }
});

/**
 * Updates the console content periodically.
 */
function UpdateConsolePeriodically() {

    // Get the console element
    let console = document.querySelector('#console');
    if (console === null) return;

    // Creates an AJAX request to get the console content
    let ajax = createAjaxRequestFor('/app/php/operations/get_console.php');
    ajax.onreadystatechange = function() {

        if (ajax.status === 200 && ajax.readyState === 4 && ajax.responseText !== "") {
            console.innerHTML += ajax.responseText;
            console.scrollTop = console.scrollHeight;
        }
    };

    // Sends the AJAX request
    ajax.send("server_uuid=" + serverUUID + "&limit=1000");
}

/**
 * Sends a message to the server.
 */
function SendMessage() {

    // Get the message element
    let message = document.querySelector('#inputField');

    let ajax = createAjaxRequestFor('/app/php/operations/send_message.php');
    ajax.send("server_uuid=" + serverUUID + "&message=" + message.innerText);

    message.innerText = "";
}

