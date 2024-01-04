<?php
    include $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/requests.php';
    
    $manager = getManagerFromConfig();
    $session_id = $_COOKIE['session_id'];
    $user = getUserInfoFromSession($manager, $session_id);
    $manager->getConnector()->close();
    
    // Gets the server information from the POST request.
    $server_id = $_POST['server_uuid'];
    $server_name = $_POST['server_name'];
    $server_type = $_POST['server_type'];
    $server_version = $_POST['server_version'];
    $server_java_version = $_POST['server_java_version'];
    $modal_id = $_POST['modal_id'];

