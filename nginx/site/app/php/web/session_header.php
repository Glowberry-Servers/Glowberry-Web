<?php
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/database_utils.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';

    // If the user is not logged in, redirect them to the welcome page.
    $manager = getManagerFromConfig();
    
    if (!isset($_COOKIE['session_id']) || !sessionCheck($manager, $_COOKIE['session_id'])) {
        header("Location: /reception/welcome.html");
        exit();
    }
    
    $manager->getConnector()->close();

