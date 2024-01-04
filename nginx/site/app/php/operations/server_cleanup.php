<?php
    include_once $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/requests.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    
    $manager = getManagerFromConfig();
    $session_id = $_COOKIE['session_id'];
    $user = getUserInfoFromSession($manager, $session_id);
    
    // Gets all the servers the user is part of
    $servers = $manager->selectWithCondition(array("server_uuid"), "ServerUser", "user_tag = '{$user['user_tag']}'");
    
    foreach ($servers as $server) {
        
        // Tries to get the server information. If it fails, then the server is deleted from the database.
        $valid = getServerInformation($server['server_uuid']);
        if ($valid) continue;
        
        $manager->deleteFrom("ServerUser", "server_uuid = '{$server['server_uuid']}'");
        $manager->deleteFrom("Server", "server_uuid = '{$server['server_uuid']}'");
    }
    
    $manager->getConnector()->close();
    
