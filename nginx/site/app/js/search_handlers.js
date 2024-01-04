document.addEventListener('DOMContentLoaded', function() {

    // Handles the search input for the servers page.
    const serversSearchInput = document.querySelector('#servers-search-input');
    if (serversSearchInput !== null) setInterval(UpdateServerSearchPeriodically, 500);

    // Handles the search input for the users page.
    const usersSearchInput = document.querySelector('#users-search-input');
    if (usersSearchInput !== null) setInterval(UpdateUserSearchPeriodically, 500);
});

/**
 * Updates the listed servers according to the search input periodically by hiding the
 * shown servers that don't match the search input.
 * @return {Promise<void>} A promise that resolves when the search is done.
 */
async function UpdateServerSearchPeriodically() {

    let searchQuery = document.querySelector('#servers-search-input').value;
    let servers = document.querySelectorAll('.server-card-name');

    await Promise.all(Array.from(servers).map(async serverName => {

        // If the server name contains the search query, show it, otherwise hide it.
        if (serverName.innerText.toLowerCase().includes(searchQuery.toLowerCase()))
            serverName.parentElement.parentElement.parentElement.style.display = 'block';

        else serverName.parentElement.parentElement.parentElement.style.display = 'none';
    }));
}

/**
 * Updates the listed users according to the search input periodically by hiding the shown users
 * that don't match the search input.
 * @return {Promise<void>} A promise that resolves when the search is done.
 */
async function UpdateUserSearchPeriodically() {

    let searchQuery = document.querySelector('#users-search-input').value;
    let users = document.querySelectorAll('.user-card-tag');

    await Promise.all(Array.from(users).map(async userTag => {

        let tag = userTag.innerText.replace('@', '');
        searchQuery = searchQuery.replace('@', '');

        // If the user's name contains the search query, show it, otherwise hide it.
        if (tag.toLowerCase().includes(searchQuery.toLowerCase()))
            userTag.parentElement.parentElement.parentElement.parentElement.style.display = 'block';

        else userTag.parentElement.parentElement.parentElement.parentElement.style.display = 'none';
    }));

}