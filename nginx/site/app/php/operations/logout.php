<?php
    
    include $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/database_utils.php';
    
    $session_id = $_COOKIE['session_id'];
    $manager = getManagerFromConfig();
    
    $manager->deleteFrom('ApplicationSession', "session_id = '$session_id'");
    setcookie("session_id", "", time() - 3600, "/");
    
    $manager->getConnector()->close();
    header("Location: /reception/index.php");