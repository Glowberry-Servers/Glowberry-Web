<?php
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/database_utils.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';

    // If the user is not logged in, redirect them to the welcome page.
    if (!isset($_COOKIE['session_id']) || !sessionCheck(getManagerFromConfig(), $_COOKIE['session_id'])) {
        header("Location: /reception/welcome.html");
        exit();
    }

    $session_id = $_COOKIE['session_id'];
    $user_info = getUserInfoFromSession(getManagerFromConfig(), $session_id);

    echo 'Logged in as '.$user_info['nickname'];
    echo '<br>';
    echo 'Session ID: '.$session_id;
