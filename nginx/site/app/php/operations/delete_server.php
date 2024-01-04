<?php
    
    include_once $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/requests.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/operations/server_cleanup.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/permissions_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/server_handler.php';
    
    $manager = getManagerFromConfig();
    $session_id = $_COOKIE['session_id'];
    $user = getUserInfoFromSession($manager, $session_id);

    $server_name = $_POST['server_name'];
    $server_id = getServerUUIDFromName($server_name);
    
    // Checks if the user has the required permissions to delete the server.
    if (!userHasServerPermission($user['user_tag'], $server_name, -1) &&
        !userHasWebPermission($user['user_tag'], 64) && $user['user_tag'] != "admin") {
        
        http_response_code(200);
        echo json_encode(array("error" => "You do not have permission to delete this server.", "alert" => true));
        exit();
    }
    
    // Checks if the server is running.
    if (isServerRunning($server_id)) {
        http_response_code(200);
        echo json_encode(array("error" => "The server is currently running. Please stop the server before deleting it.", "alert" => true));
        exit();
    }
    
    // Checks if the server is owned by the admin. If so, only the admin can delete it.
    if (getServerOwner($server_id) == "admin" && $user['user_tag'] != "admin") {
        http_response_code(200);
        echo json_encode(array("error" => "Only the admin account can delete their servers.", "alert" => true));
        exit();
    }
    
    // Deletes the server using the API
    deleteServer($server_id);
    
    // Deletes the server from the database.
    $manager->deleteFrom("ServerUser", "server_uuid = '$server_id'");
    $manager->deleteFrom("Server", "server_uuid = '$server_id'");
    
    $manager->getConnector()->close();
    
    http_response_code(200);
    echo json_encode(array("success" => "Successfully deleted server.", "method" => "POST",
        "href" => "/app/php/web/dashboard.php"));
    
    exit();